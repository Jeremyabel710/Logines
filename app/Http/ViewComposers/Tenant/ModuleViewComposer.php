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
            // Redirige al login si no hay sesión activa
            redirect()->route('login')->send();
            exit;
        }
    
        $user = Auth::user();
    
        // Verificar si el usuario tiene el método 'modules'
        if (!method_exists($user, 'modules')) {
            throw new \Exception("El usuario autenticado no tiene el método 'modules()'. Revisa el modelo User.");
        }
    
        $modules = $user->modules()->pluck('value')->toArray();
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
