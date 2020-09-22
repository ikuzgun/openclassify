<?php namespace Visiosoft\AdvsModule;

use Anomaly\FilesModule\File\FileModel;
use Anomaly\Streams\Platform\Addon\AddonCollection;
use Anomaly\Streams\Platform\Addon\AddonServiceProvider;
use Anomaly\Streams\Platform\Model\Location\LocationVillageEntryModel;
use Anomaly\Streams\Platform\Ui\Table\Event\TableIsQuerying;
use Visiosoft\AdvsModule\Adv\Contract\AdvRepositoryInterface;
use Visiosoft\AdvsModule\Adv\AdvRepository;
use Anomaly\Streams\Platform\Model\Advs\AdvsAdvsEntryModel;
use Visiosoft\AdvsModule\Adv\AdvModel;
use Visiosoft\AdvsModule\Adv\Form\AdvFormBuilder;
use Visiosoft\AdvsModule\Http\Middleware\redirectDiffrentLang;
use Visiosoft\AdvsModule\Http\Middleware\SetLang;
use Visiosoft\AdvsModule\Listener\AddAdvsSettingsScript;
use Visiosoft\AdvsModule\Option\Contract\OptionRepositoryInterface;
use Visiosoft\AdvsModule\Option\OptionRepository;
use Visiosoft\LocationModule\Village\Contract\VillageRepositoryInterface;
use Visiosoft\LocationModule\Village\VillageRepository;
use Visiosoft\LocationModule\Village\VillageModel;
use Visiosoft\CatsModule\Category\Contract\CategoryRepositoryInterface;
use Visiosoft\CatsModule\Category\CategoryRepository;
use Visiosoft\CatsModule\Category\CategoryModel;
use Illuminate\Routing\Router;
use Visiosoft\LocationModule\Country\Contract\CountryRepositoryInterface;
use Visiosoft\LocationModule\Country\CountryRepository;

class AdvsModuleServiceProvider extends AddonServiceProvider
{

    /**
     * Additional addon plugins.
     *
     * @type array|null
     */
    protected $plugins = [
        AdvsModulePlugin::class,
    ];

    /**
     * The addon Artisan commands.
     *
     * @type array|null
     */
    protected $commands = [];

    /**
     * The addon's scheduled commands.
     *
     * @type array|null
     */
    protected $schedules = [];

    /**
     * The addon API routes.
     *
     * @type array|null
     */
    protected $api = [];

    /**
     * The addon routes.
     *
     * @type array|null
     */
    protected $routes = [
        // Admin AdvsController
        'admin/advs' => [
            'as' => 'visiosoft.module.advs::admin_advs',
            'uses' => 'Visiosoft\AdvsModule\Http\Controller\Admin\AdvsController@index',
        ],
        'admin/assets/clear' => [
            'as' => 'assets_clear',
            'uses' => 'Visiosoft\AdvsModule\Http\Controller\Admin\AdvsController@assetsClear',
        ],
        'admin/advs-users/choose/{advId}' => 'Visiosoft\AdvsModule\Http\Controller\Admin\AdvsController@choose',
        'admin/class/actions/{id}/{type}' => 'Visiosoft\AdvsModule\Http\Controller\Admin\AdvsController@actions',

        // advsController
        'advs/list' => [
            'as' => 'visiosoft.module.advs::list',
            'uses' => 'Visiosoft\AdvsModule\Http\Controller\advsController@index'
        ],
        'advs/list?user={id}' => [
            'as' => 'visiosoft.module.advs::list_user_ad',
            'uses' => 'Visiosoft\AdvsModule\Http\Controller\advsController@index',
        ],
        'advs/list?cat={id}' => [
            'as' => 'visiosoft.module.advs::list_cat',
            'uses' => 'Visiosoft\AdvsModule\Http\Controller\advsController@index',
        ],
        'advs/adv/{id}' => [
            'as' => 'adv_detail_backup',
            'uses' => 'Visiosoft\AdvsModule\Http\Controller\advsController@view'
        ],
        'advs/adv/{id}/{seo}' => [
            'as' => 'adv_detail_seo_backup',
            'uses' => 'Visiosoft\AdvsModule\Http\Controller\advsController@view'
        ],
        'ad/{id}' => [
            'as' => 'adv_detail',
            'uses' => 'Visiosoft\AdvsModule\Http\Controller\advsController@view'
        ],
        'ad/{seo}/{id}' => [
            'as' => 'adv_detail_seo',
            'uses' => 'Visiosoft\AdvsModule\Http\Controller\advsController@view'
        ],
        'advs/preview/{id}' => [
            'as' => 'advs_preview',
            'uses' => 'Visiosoft\AdvsModule\Http\Controller\advsController@preview'
        ],
        'advs/map?country={country}&city[]={city}&district={districts}' => [
            'as' => 'visiosoft.module.advs::show_ad_map_location',
            'uses' => 'Visiosoft\AdvsModule\Http\Controller\advsController@index'
        ],
        'c/{category?}/{city?}' => [
            'as' => 'adv_list_seo',
            'uses' => 'Visiosoft\AdvsModule\Http\Controller\advsController@index'
        ],
        'advs/create_adv' => [
            'as' => "advs::create_adv",
            'uses' => 'Visiosoft\AdvsModule\Http\Controller\advsController@cats',
        ],
        'advs/create_adv/post_cat' => [
            'as' => 'post_adv',
            'uses' => 'Visiosoft\AdvsModule\Http\Controller\advsController@create',
        ],
        'advs/save_adv' => [
            'as' => 'visiosoft.module.advs::post_cat',
            'uses' => 'Visiosoft\AdvsModule\Http\Controller\advsController@store'
        ],
        'advs/edit_advs/{id}' => [
            'as' => 'visiosoft.module.advs::edit_adv',
            'uses' => 'Visiosoft\AdvsModule\Http\Controller\advsController@edit',
        ],
        'advs/status/{id},{type}' => [
            'as' => 'visiosoft.module.advs::status',
            'uses' => 'Visiosoft\AdvsModule\Http\Controller\advsController@statusAds'
        ],
        'advs/delete/{id}' => [
            'as' => 'advs::delete',
            'uses' => 'Visiosoft\AdvsModule\Http\Controller\advsController@deleteAd',
        ],
        'adv/addCart/{id}' => [
            'as' => 'adv_AddCart',
            'uses' => 'Visiosoft\AdvsModule\Http\Controller\advsController@advAddCart',
        ],
        'ajax/StockControl' => [
            'as' => 'adv_stock_control_ajax',
            'uses' => 'Visiosoft\AdvsModule\Http\Controller\advsController@stockControl',
        ],
        'ajax/addCart' => [
            'as' => 'adv_add_cart_ajax',
            'uses' => 'Visiosoft\AdvsModule\Http\Controller\advsController@addCart',
        ],
        'ajax/countPhone' => [
            'as' => 'adv_count_show_phone',
            'uses' => 'Visiosoft\AdvsModule\Http\Controller\advsController@showPhoneCounter',
        ],
        'view/{type}' => [
            'as' => 'visiosoft.module.advs::view_type',
            'uses' => 'Visiosoft\AdvsModule\Http\Controller\advsController@viewType',
        ],
        'adv/edit/category/{id}' => [
            'as' => 'adv::edit_category',
            'uses' => 'Visiosoft\AdvsModule\Http\Controller\advsController@editCategoryForAd',
        ],
        'ajax/getcats/{id}' => [
            'as' => 'ajax::getCats',
            'uses' => 'Visiosoft\AdvsModule\Http\Controller\advsController@getCats',
        ],
        'advs/extendAll/{isAdmin?}' => [
            'as' => 'advs::extendAll',
            'uses' => 'Visiosoft\AdvsModule\Http\Controller\AdvsController@extendAll',
        ],
        'advs/extend/{adId}' => [
            'as' => 'advs::extendSingle',
            'uses' => 'Visiosoft\AdvsModule\Http\Controller\AdvsController@extendSingle',
        ],
        'categories/checkparent/{id}' => 'Visiosoft\AdvsModule\Http\Controller\advsController@checkParentCat',
        'getlocations' => 'Visiosoft\AdvsModule\Http\Controller\advsController@getLocations',
        'class/getcats/{id}' => 'Visiosoft\AdvsModule\Http\Controller\advsController@getCatsForNewAd',
        'mapJson' => 'Visiosoft\AdvsModule\Http\Controller\advsController@mapJson',
        'check_user' => 'Visiosoft\AdvsModule\Http\Controller\advsController@checkUser',

        // AjaxController
        'admin/advs/ajax' => [
            'as' => 'visiosoft.module.advs::ajax',
            'uses' => 'Visiosoft\AdvsModule\Http\Controller\AjaxController@locations',
        ],
        'ajax/viewed/{id}' => [
            'as' => 'advs::viewed',
            'uses' => 'Visiosoft\AdvsModule\Http\Controller\AjaxController@viewed',
        ],
        'ajax/getAdvs' => [
            'as' => 'ajax::getAds',
            'uses' => 'Visiosoft\AdvsModule\Http\Controller\AjaxController@getMyAds'
        ],
        'ajax/get-advs-by-category/{categoryID}' => [
            'as' => 'ajax::getAds',
            'uses' => 'Visiosoft\AdvsModule\Http\Controller\AjaxController@getAdvsByCat'
        ],
        'class/ajax' => 'Visiosoft\AdvsModule\Http\Controller\AjaxController@locations',
        'class/ajaxCategory' => 'Visiosoft\AdvsModule\Http\Controller\AjaxController@categories',
        'keySearch' => 'Visiosoft\AdvsModule\Http\Controller\AjaxController@keySearch',

        // CategoriesController
        'advs/c/{cat}' => 'Visiosoft\AdvsModule\Http\Controller\CategoriesController@listByCat',

        // Others
        'advs/ttr/{id}' => 'Visiosoft\PackagesModule\Http\Controller\packageFEController@advsStatusbyUser',
    ];

    /**
     * The addon middleware.
     *
     * @type array|null
     */
    protected $middleware = [
        SetLang::class,
        redirectDiffrentLang::class,
    ];

    /**
     * Addon group middleware.
     *
     * @var array
     */
    protected $groupMiddleware = [
        //'web' => [
        //    Visiosoft\AdvsModule\Http\Middleware\ExampleMiddleware::class,
        //],
    ];

    /**
     * Addon route middleware.
     *
     * @type array|null
     */
    protected $routeMiddleware = [];

    /**
     * The addon event listeners.
     *
     * @type array|null
     */
    protected $listeners = [
        TableIsQuerying::class => [
            AddAdvsSettingsScript::class,
        ],
    ];

    /**
     * The addon alias bindings.
     *
     * @type array|null
     */
    protected $aliases = [
        //'Example' => Visiosoft\AdvsModule\Example::class
    ];

    /**
     * The addon class bindings.
     *
     * @type array|null
     */
    protected $bindings = [
        // AdvsCfValuesEntryModel::class => CfValueModel::class,
        // AdvsCustomFieldAdvsEntryModel::class => CustomFieldAdvModel::class,
        // AdvsCustomFieldsEntryModel::class => CustomFieldModel::class,
        AdvsAdvsEntryModel::class => AdvModel::class,
        LocationVillageEntryModel::class => VillageModel::class,
        AdvsCategoriesEntryModel::class => CategoryModel::class,
        AdvsAdvsEntryModel::class => AdvModel::class,
        'my_form' => AdvFormBuilder::class,
    ];

    /**
     * The addon singleton bindings.
     *
     * @type array|null
     */
    protected $singletons = [
        // CfValueRepositoryInterface::class => CfValueRepository::class,
        // CustomFieldAdvRepositoryInterface::class => CustomFieldAdvRepository::class,
        // CustomFieldRepositoryInterface::class => CustomFieldRepository::class,
        AdvRepositoryInterface::class => AdvRepository::class,
        VillageRepositoryInterface::class => VillageRepository::class,
        CategoryRepositoryInterface::class => CategoryRepository::class,
        CountryRepositoryInterface::class => CountryRepository::class,
        OptionRepositoryInterface::class => OptionRepository::class,
    ];

    /**
     * Additional service providers.
     *
     * @type array|null
     */
    protected $providers = [
        //\ExamplePackage\Provider\ExampleProvider::class
    ];

    /**
     * The addon view overrides.
     *
     * @type array|null
     */
    protected $overrides = [
        'streams::form/form' => 'visiosoft.module.advs::form/form',
        //'streams::errors/404' => 'module::errors/404',
        //'streams::errors/500' => 'module::errors/500',
    ];

    /**
     * The addon mobile-only view overrides.
     *
     * @type array|null
     */
    protected $mobile = [
        //'streams::errors/404' => 'module::mobile/errors/404',
        //'streams::errors/500' => 'module::mobile/errors/500',
    ];

    /**
     * Register the addon.
     */
    public function register()
    {
        // Run extra pre-boot registration logic here.
        // Use method injection or commands to bring in services.
    }

    /**
     * Boot the addon.
     * @param AddonCollection $addonCollection
     * @param FileModel $fileModel
     */
    public function boot(AddonCollection $addonCollection, FileModel $fileModel)
    {
        // Run extra post-boot registration logic here.
        // Use method injection or commands to bring in services.
        $settings_url = [
            'general_settings' => [
                'title' => 'visiosoft.module.advs::button.general_settings',
                'href' => '/admin/settings/modules/visiosoft.module.advs',
            ],
            'theme_settings' => [
                'title' => 'visiosoft.theme.defaultadmin::section.theme_settings.name',
                'href' => url('admin/settings/themes/' . setting_value('streams::standard_theme')),
            ],
            'assets_clear' => [
                'title' => 'visiosoft.module.advs::section.assets_clear.name',
                'href' => route('assets_clear'),
            ],
        ];

        foreach ($settings_url as $key => $value) {
            $addonCollection->get('anomaly.module.settings')->addSection($key, $value);
        }

        // Disable file versioning
        $fileModel->disableVersioning();
    }

    /**
     * Map additional addon routes.
     *
     * @param Router $router
     */
    // public function map(Router $router)
    // {
    //     // Register dynamic routes here for example.
    //     // Use method injection or commands to bring in services.
    // }
    public function map(Router $router)
    {
    }
}
