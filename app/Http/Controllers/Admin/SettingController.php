<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Setting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class SettingController extends Controller
{
    public function index()
    {
        if (!auth('admin')->user()->isIngeniero() && !auth('admin')->user()->isAdmin()) {
            abort(403, 'No tienes permiso para acceder a la configuración.');
        }

        $settings = [
            'sunat_ruc' => Setting::get('sunat_ruc', '20123456789'),
            'sunat_user' => Setting::get('sunat_user', 'MODDATOS'),
            'sunat_pass' => Setting::get('sunat_pass', 'MODDATOS'),
            'company_name' => Setting::get('company_name', 'GOURMETICA S.A.C.'),
            'company_address' => Setting::get('company_address', 'AV. LAS ARTES 123'),
            'culqi_public_key' => Setting::get('culqi_public_key', ''),
            'culqi_private_key' => Setting::get('culqi_private_key', ''),
            'contact_whatsapp' => Setting::get('contact_whatsapp', '950664655'),
            'contact_email' => Setting::get('contact_email', 'hola@gourmetica.pe'),
            'contact_phone' => Setting::get('contact_phone', '(01) 234-5678'),
            'nakama_api_url' => Setting::get('nakama_api_url', 'http://localhost/public'),
            'nakama_api_key' => Setting::get('nakama_api_key', ''),
            'nakama_enabled' => Setting::get('nakama_enabled', '0'),
        ];

        return view('admin.settings.index', compact('settings'));
    }

    public function update(Request $request)
    {
        if (!auth('admin')->user()->isIngeniero() && !auth('admin')->user()->isAdmin()) {
            abort(403, 'No tienes permiso para realizar esta acción.');
        }

        $allowedKeys = [
            'company_name',
            'company_address',
            'contact_whatsapp',
            'contact_email',
            'contact_phone'
        ];

        if (!auth('admin')->user()->isIngeniero()) {
            // General admin can only update company and contact settings
            $requestData = $request->only($allowedKeys);
        } else {
            // Engineer can update everything
            $requestData = $request->except(['_token', 'sunat_certificate']);
            // Handle checkbox for nakama_enabled explicitly
            $requestData['nakama_enabled'] = $request->has('nakama_enabled') ? '1' : '0';
        }

        foreach ($requestData as $key => $value) {
            $group = 'general';
            if (str_contains($key, 'sunat')) {
                $group = 'sunat';
            } elseif (str_contains($key, 'culqi')) {
                $group = 'culqi';
            } elseif (str_contains($key, 'nakama')) {
                $group = 'nakama';
            } else {
                $group = 'company';
            }

            Setting::set($key, $value, $group);
        }

        if (auth('admin')->user()->isIngeniero() && $request->hasFile('sunat_certificate')) {
            $path = $request->file('sunat_certificate')->storeAs('sunat', 'certificate.pem');
            Setting::set('sunat_certificate_path', $path, 'sunat');
        }

        return back()->with('success', 'Configuración actualizada correctamente.');
    }
}
