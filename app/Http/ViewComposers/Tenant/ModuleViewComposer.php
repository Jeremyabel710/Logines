<?php

namespace App\Http\ViewComposers\Tenant;

use App\Models\System\Configuration;
use App\Models\Tenant\Configuration as TenantConfiguration;
use App\Models\Tenant\Module;
use Illuminate\Support\Facades\Auth;

class ModuleViewComposer
{
    public function compose($view)
    {
        if (!Auth::check()) {
            // Redirigir al login o establecer valores predeterminados
            redirect()->route('login')->send();
            exit;
        }

        /** @var \App\Models\Tenant\User $user */
        $user = Auth::user();

        // Verificar si el usuario tiene el método modules()
        if (method_exists($user, 'modules')) {
            $modules = $user->modules()->pluck('value')->toArray();
        } else {
            $modules = [];
        }
        /*
        $systemConfig = Configuration::select('use_login_global')->first();
        */
        $systemConfig = Configuration::getDataModuleViewComposer();

        if(count($modules) > 0) {
            $view->vc_modules = $modules;
        } else {
            $view->vc_modules = Module::all()->pluck('value')->toArray();
        }
        $view->vc_configuration = TenantConfiguration::first();

        $view->useLoginGlobal = $systemConfig->use_login_global;

        $view->tenant_show_ads = $systemConfig->tenant_show_ads;
        $view->url_tenant_image_ads = $systemConfig->getUrlTenantImageAds();

    }
}
