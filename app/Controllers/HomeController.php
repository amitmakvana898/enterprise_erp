<?php

namespace App\Controllers;

use App\Core\Controller;

class HomeController extends Controller {
    public function index(): void {
        $this->render('home/index', [
            'title' => 'Enterprise Multi-Branch ERP System',
            'meta_description' => 'Scalable Enterprise Multi-Branch Inventory, Procurement & Sales ERP System built in PHP MVC'
        ], 'layouts/landing');
    }

    public function about(): void {
        $this->render('home/about', [
            'title' => 'About System - Enterprise ERP Architecture',
            'meta_description' => 'Detailed system architecture, security framework, database relational model, and enterprise capabilities.'
        ], 'layouts/landing');
    }
}
