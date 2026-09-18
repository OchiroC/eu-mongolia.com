<?php

namespace App\Http\Controllers;

use Inertia\Inertia;
use Inertia\Response;

class PageController extends Controller
{
    public function about(): Response
    {
        return Inertia::render('Static/About', [
            'seo' => [
                'title' => 'Бидний тухай | '.config('app.name'),
                'description' => config('app.name').' бол Франкфурт болон ойр орчмын монголчуудын мэдээллийн сайт.',
            ],
        ]);
    }

    public function contact(): Response
    {
        return Inertia::render('Static/Contact', [
            'seo' => [
                'title' => 'Холбоо барих | '.config('app.name'),
                'description' => 'Бидэнтэй холбогдох. Санал, гомдол, хамтын ажиллагаа, реклам байршуулах.',
            ],
        ]);
    }

    public function terms(): Response
    {
        return Inertia::render('Static/Terms', [
            'seo' => ['title' => 'Үйлчилгээний нөхцөл | '.config('app.name')],
        ]);
    }

    public function privacy(): Response
    {
        return Inertia::render('Static/Privacy', [
            'seo' => ['title' => 'Нууцлалын бодлого | '.config('app.name')],
        ]);
    }
}
