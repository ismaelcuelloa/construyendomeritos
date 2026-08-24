<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Course;

class SitemapController extends Controller
{
    public function index()
    {
        $sitemap = '<?xml version="1.0" encoding="UTF-8"?>';
        $sitemap .= '<sitemapindex xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">';

        // Sitemap principal
        $sitemap .= '<sitemap>';
        $sitemap .= '<loc>'.url('/sitemap-pages.xml').'</loc>';
        $sitemap .= '<lastmod>'.now()->toAtomString().'</lastmod>';
        $sitemap .= '</sitemap>';

        // Sitemap de cursos
        $sitemap .= '<sitemap>';
        $sitemap .= '<loc>'.url('/sitemap-courses.xml').'</loc>';
        $sitemap .= '<lastmod>'.now()->toAtomString().'</lastmod>';
        $sitemap .= '</sitemap>';

        // Sitemap de categorías
        $sitemap .= '<sitemap>';
        $sitemap .= '<loc>'.url('/sitemap-categories.xml').'</loc>';
        $sitemap .= '<lastmod>'.now()->toAtomString().'</lastmod>';
        $sitemap .= '</sitemap>';

        $sitemap .= '</sitemapindex>';

        return response($sitemap, 200)
            ->header('Content-Type', 'application/xml');
    }

    public function pages()
    {
        $sitemap = '<?xml version="1.0" encoding="UTF-8"?>';
        $sitemap .= '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">';

        // Páginas estáticas
        $pages = [
            ['url' => '/', 'priority' => '1.0', 'changefreq' => 'daily'],
            ['url' => '/cursos', 'priority' => '0.9', 'changefreq' => 'daily'],
            ['url' => '/categorias', 'priority' => '0.8', 'changefreq' => 'weekly'],
            ['url' => '/terminos-de-servicio', 'priority' => '0.3', 'changefreq' => 'monthly'],
            ['url' => '/politica-de-privacidad', 'priority' => '0.3', 'changefreq' => 'monthly'],
        ];

        foreach ($pages as $page) {
            $sitemap .= '<url>';
            $sitemap .= '<loc>'.url($page['url']).'</loc>';
            $sitemap .= '<lastmod>'.now()->toAtomString().'</lastmod>';
            $sitemap .= '<changefreq>'.$page['changefreq'].'</changefreq>';
            $sitemap .= '<priority>'.$page['priority'].'</priority>';
            $sitemap .= '</url>';
        }

        $sitemap .= '</urlset>';

        return response($sitemap, 200)
            ->header('Content-Type', 'application/xml');
    }

    public function courses()
    {
        $sitemap = '<?xml version="1.0" encoding="UTF-8"?>';
        $sitemap .= '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9" ';
        $sitemap .= 'xmlns:image="http://www.google.com/schemas/sitemap-image/1.1">';

        $courses = Course::query()
            ->with('metadata')
            ->published()
            ->visible()
            ->get();

        foreach ($courses as $course) {
            $sitemap .= '<url>';
            $sitemap .= '<loc>'.url('/cursos/'.$course->slug).'</loc>';
            $sitemap .= '<lastmod>'.$course->updated_at->toAtomString().'</lastmod>';
            $sitemap .= '<changefreq>weekly</changefreq>';
            $sitemap .= '<priority>0.8</priority>';

            // Agregar imagen si existe
            if ($course->metadata && $course->metadata->banner) {
                $sitemap .= '<image:image>';
                $sitemap .= '<image:loc>'.url($course->metadata->banner).'</image:loc>';
                $sitemap .= '<image:title>'.htmlspecialchars($course->title).'</image:title>';
                $sitemap .= '</image:image>';
            }

            $sitemap .= '</url>';
        }

        $sitemap .= '</urlset>';

        return response($sitemap, 200)
            ->header('Content-Type', 'application/xml');
    }

    public function categories()
    {
        $sitemap = '<?xml version="1.0" encoding="UTF-8"?>';
        $sitemap .= '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">';

        $categories = Category::query()
            ->published()
            ->get();

        foreach ($categories as $category) {
            $sitemap .= '<url>';
            $sitemap .= '<loc>'.url('/categorias/'.$category->slug).'</loc>';
            $sitemap .= '<lastmod>'.$category->updated_at->toAtomString().'</lastmod>';
            $sitemap .= '<changefreq>weekly</changefreq>';
            $sitemap .= '<priority>0.7</priority>';
            $sitemap .= '</url>';
        }

        $sitemap .= '</urlset>';

        return response($sitemap, 200)
            ->header('Content-Type', 'application/xml');
    }
}
