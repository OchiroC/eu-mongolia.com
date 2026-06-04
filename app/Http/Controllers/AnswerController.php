<?php

namespace App\Http\Controllers;

use App\Models\Answer;
use App\Models\AnswerVote;
use App\Models\Question;
use App\Notifications\NewAnswer;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class AnswerController extends Controller
{
    public function store(Request $request, Question $question): RedirectResponse
    {
        $data = $request->validate(['body' => ['required', 'string', 'max:5000']]);

        $answer = $question->answers()->create([
            'user_id' => $request->user()->id,
            'body' => $data['body'],
        ]);
        $question->increment('answers_count');

        // Асуулт эзэмшигчид мэдэгдэнэ (өөртөө биш).
        if ($question->user_id && $question->user_id !== $request->user()->id) {
            $question->user?->notify(new NewAnswer($answer, $question->slug, $question->title));
        }

        return back()->with('success', 'Хариулт нэмэгдлээ.');
    }

    /** Тус болсон гэж санал өгөх (toggle). */
    public function vote(Request $request, Answer $answer): RedirectResponse
    {
        $existing = AnswerVote::where('answer_id', $answer->id)->where('user_id', $request->user()->id)->first();

        if ($existing) {
            $existing->delete();
        } else {
            AnswerVote::create(['answer_id' => $answer->id, 'user_id' => $request->user()->id]);
        }

        $answer->update(['votes_count' => AnswerVote::where('answer_id', $answer->id)->count()]);

        return back();
    }

    /** Асуулт эзэмшигч шилдэг хариултыг тэмдэглэнэ (toggle). */
    public function accept(Request $request, Answer $answer): RedirectResponse
    {
        $question = $answer->question;
        abort_unless($question && $question->user_id === $request->user()->id, 403);

        $question->update([
            'best_answer_id' => $question->best_answer_id === $answer->id ? null : $answer->id,
        ]);

        return back();
    }

    public function destroy(Request $request, Answer $answer): RedirectResponse
    {
        abort_unless($answer->user_id === $request->user()->id, 403);

        $question = $answer->question;
        if ($question && $question->best_answer_id === $answer->id) {
            $question->update(['best_answer_id' => null]);
        }
        $answer->delete();
        $question?->decrement('answers_count');

        return back()->with('success', 'Хариулт устгагдлаа.');
    }
}
