<?php

namespace App\Http\Controllers;

abstract class Controller
{
    /**
     * Render an admin-panel page from the folder that matches the current
     * session. The config-based super admin (session('is_super_admin'))
     * gets resources/views/superAdmin/*; a staff or dentist account gets
     * the resources/views/admin/* copy — same page, trimmed-down menu.
     */
    protected function panelView(string $view, array $data = [])
    {
        $folder = session('is_super_admin') ? 'superAdmin' : 'admin';

        return view("{$folder}.{$view}", $data);
    }

}
