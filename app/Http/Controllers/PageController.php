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
                'title' => 'Бидний тухай — EU Mongolia',
                'description' => 'EU Mongolia — Европ дахь монголчуудын зар, мэдээ, эвентийн нэгдсэн платформ.',
            ],
        ]);
    }

    public function contact(): Response
    {
        return Inertia::render('Static/Contact', [
            'seo' => [
                'title' => 'Холбоо барих — EU Mongolia',
                'description' => 'EU Mongolia-тэй холбогдох. Санал, гомдол, хамтын ажиллагаа, реклам байршуулах.',
            ],
        ]);
    }

    public function terms(): Response
    {
        return Inertia::render('Static/Terms', [
            'seo' => ['title' => 'Үйлчилгээний нөхцөл — EU Mongolia'],
        ]);
    }

    public function privacy(): Response
    {
        return Inertia::render('Static/Privacy', [
            'seo' => ['title' => 'Нууцлалын бодлого — EU Mongolia'],
        ]);
    }
}
