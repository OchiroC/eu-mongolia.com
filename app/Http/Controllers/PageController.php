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
                'title' => 'Бидний тухай — Yazguur',
                'description' => 'Yazguur — Европ дахь монголчуудын зар, мэдээ, эвентийн нэгдсэн платформ.',
            ],
        ]);
    }

    public function contact(): Response
    {
        return Inertia::render('Static/Contact', [
            'seo' => [
                'title' => 'Холбоо барих — Yazguur',
                'description' => 'Yazguur-тэй холбогдох. Санал, гомдол, хамтын ажиллагаа, реклам байршуулах.',
            ],
        ]);
    }

    public function terms(): Response
    {
        return Inertia::render('Static/Terms', [
            'seo' => ['title' => 'Үйлчилгээний нөхцөл — Yazguur'],
        ]);
    }

    public function privacy(): Response
    {
        return Inertia::render('Static/Privacy', [
            'seo' => ['title' => 'Нууцлалын бодлого — Yazguur'],
        ]);
    }
}
