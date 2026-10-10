<?php

class RedirectSentinel extends Exception
{
    /** @var string */
    private $url;

    public function __construct($url)
    {
        $this->url = (string) $url;
        parent::__construct('redirect');
    }

    /**
     * @return string
     */
    public function getUrl()
    {
        return $this->url;
    }
}

class Module
{
    /** @var bool */
    public static $parentInstallResult = true;

    /** @var bool */
    public static $parentUninstallResult = true;

    /** @var bool */
    public static $hookRegistrationResult = true;

    /** @var array */
    public static $registeredHookCalls = array();

    /** @var Context */
    public $context;

    /** @var string */
    public $pathUri = 'modules/ps_faviconnotificationbo/';

    /** @var string */
    public $localPath = '/var/modules/ps_faviconnotificationbo/';

    public function __construct()
    {
        $this->context = Context::getInstance();
    }

    /**
     * @return string
     */
    public function getPathUri()
    {
        return $this->pathUri;
    }

    /**
     * @return string
     */
    public function getLocalPath()
    {
        return $this->localPath;
    }

    /**
     * @param string $id
     * @param array  $parameters
     * @param string $domain
     *
     * @return string
     */
    protected function trans($id, $parameters, $domain)
    {
        return $domain . '|' . $id;
    }

    public static function reset()
    {
        self::$parentInstallResult = true;
        self::$parentUninstallResult = true;
        self::$hookRegistrationResult = true;
        self::$registeredHookCalls = array();
    }

    /**
     * @return bool
     */
    public function install()
    {
        return (bool) self::$parentInstallResult;
    }

    /**
     * @return bool
     */
    public function uninstall()
    {
        return (bool) self::$parentUninstallResult;
    }

    /**
     * @param mixed $hooks
     *
     * @return bool
     */
    public function registerHook($hooks)
    {
        self::$registeredHookCalls[] = $hooks;

        return (bool) self::$hookRegistrationResult;
    }
}

class Configuration
{
    /** @var array */
    public static $values = array();

    /** @var array */
    public static $updateLog = array();

    /** @var array */
    public static $deleteLog = array();

    /** @var array */
    public static $updateResults = array();

    /** @var array */
    public static $deleteResults = array();

    public static function reset()
    {
        self::$values = array();
        self::$updateLog = array();
        self::$deleteLog = array();
        self::$updateResults = array();
        self::$deleteResults = array();
    }

    /**
     * @param string $key
     * @param mixed  $value
     *
     * @return bool
     */
    public static function updateValue($key, $value)
    {
        self::$updateLog[] = array($key, $value);
        if (array_key_exists($key, self::$updateResults) && !self::$updateResults[$key]) {
            return false;
        }
        self::$values[$key] = $value;

        return true;
    }

    /**
     * @param string $name
     *
     * @return bool
     */
    public static function deleteByName($name)
    {
        self::$deleteLog[] = $name;
        if (array_key_exists($name, self::$deleteResults) && !self::$deleteResults[$name]) {
            return false;
        }
        unset(self::$values[$name]);

        return true;
    }

    /**
     * @param string $key
     *
     * @return mixed
     */
    public static function get($key)
    {
        return array_key_exists($key, self::$values) ? self::$values[$key] : null;
    }
}

class Tab
{
    /** @var array */
    public static $idFromClassName = array();

    /** @var array */
    public static $addedTabs = array();

    /** @var array */
    public static $collectionByModule = array();

    /** @var array */
    public static $deletedTabIds = array();

    /** @var bool */
    public static $addReturn = true;

    /** @var string */
    public $class_name;

    /** @var string */
    public $module;

    /** @var bool */
    public $active;

    /** @var int */
    public $id_parent;

    /** @var array */
    public $name;

    /** @var bool */
    public $deleteResult = true;

    /** @var int */
    private static $nextId = 1;

    /** @var int */
    public $id;

    public function __construct()
    {
        $this->id = self::$nextId++;
    }

    public static function reset()
    {
        self::$idFromClassName = array();
        self::$addedTabs = array();
        self::$collectionByModule = array();
        self::$deletedTabIds = array();
        self::$addReturn = true;
        self::$nextId = 1;
    }

    /**
     * @param string $className
     *
     * @return int
     */
    public static function getIdFromClassName($className)
    {
        if (!array_key_exists($className, self::$idFromClassName)) {
            return 0;
        }

        return (int) self::$idFromClassName[$className];
    }

    /**
     * @return bool
     */
    public function add()
    {
        self::$addedTabs[] = array(
            'class_name' => $this->class_name,
            'module' => $this->module,
            'active' => $this->active,
            'id_parent' => $this->id_parent,
            'name' => $this->name,
        );

        return (bool) self::$addReturn;
    }

    /**
     * @param string $moduleName
     *
     * @return Tab[]
     */
    public static function getCollectionFromModule($moduleName)
    {
        if (!array_key_exists($moduleName, self::$collectionByModule)) {
            return array();
        }

        return self::$collectionByModule[$moduleName];
    }

    /**
     * @return bool
     */
    public function delete()
    {
        self::$deletedTabIds[] = $this->id;

        return (bool) $this->deleteResult;
    }
}

class Language
{
    /** @var array */
    public static $ids = array(1);

    /** @var array */
    public static $getIdsCalls = array();

    public static function reset()
    {
        self::$ids = array(1);
        self::$getIdsCalls = array();
    }

    /**
     * @param bool $active
     *
     * @return array
     */
    public static function getIDs($active)
    {
        self::$getIdsCalls[] = $active;

        return self::$ids;
    }
}

class Tools
{
    /** @var array */
    public static $redirectUrls = array();

    public static function reset()
    {
        self::$redirectUrls = array();
    }

    /**
     * @param string $url
     */
    public static function redirectAdmin($url)
    {
        self::$redirectUrls[] = $url;
        throw new RedirectSentinel($url);
    }
}

class Link
{
    /** @var string */
    public static $adminUrlSentinel = 'https://opaque-admin-url.test/sentinel';

    /** @var array */
    public static $getAdminLinkCalls = array();

    public static function reset()
    {
        self::$adminUrlSentinel = 'https://opaque-admin-url.test/sentinel';
        self::$getAdminLinkCalls = array();
    }

    /**
     * @param string $controller
     *
     * @return string
     */
    public function getAdminLink($controller)
    {
        self::$getAdminLinkCalls[] = $controller;

        return self::$adminUrlSentinel;
    }
}

class Smarty
{
    /** @var array */
    public static $assignments = array();

    /** @var string|null */
    public static $lastFetchPath;

    /** @var string */
    public static $fetchReturnSentinel = 'opaque-smarty-fetch-sentinel';

    public static function reset()
    {
        self::$assignments = array();
        self::$lastFetchPath = null;
        self::$fetchReturnSentinel = 'opaque-smarty-fetch-sentinel';
    }

    /**
     * @param array $vars
     */
    public function assign($vars)
    {
        self::$assignments = $vars;
    }

    /**
     * @param string $path
     *
     * @return string
     */
    public function fetch($path)
    {
        self::$lastFetchPath = $path;

        return self::$fetchReturnSentinel;
    }
}

class Controller
{
    /** @var array */
    public static $jsFiles = array();

    public static function reset()
    {
        self::$jsFiles = array();
    }

    /**
     * @param array $files
     */
    public function addJS($files)
    {
        self::$jsFiles[] = $files;
    }
}

class Context
{
    /** @var Context */
    private static $instance;

    /** @var Controller */
    public $controller;

    /** @var Smarty */
    public $smarty;

    /** @var Link */
    public $link;

    public static function reset()
    {
        self::$instance = null;
    }

    /**
     * @return Context
     */
    public static function getInstance()
    {
        if (self::$instance === null) {
            self::$instance = new self();
            self::$instance->controller = new Controller();
            self::$instance->smarty = new Smarty();
            self::$instance->link = new Link();
        }

        return self::$instance;
    }
}

class ModuleAdminController
{
    /** @var bool */
    public $bootstrap;

    /** @var string */
    public $className;

    /** @var string */
    public $table;

    /** @var array */
    public $fields_options;

    public function __construct()
    {
    }

    /**
     * @param string $id
     * @param array  $parameters
     * @param string $domain
     *
     * @return string
     */
    protected function trans($id, $parameters, $domain)
    {
        return $domain . '|' . $id;
    }
}

class Meta
{
    /** @var bool */
    public $deleteResult = true;

    /** @var array */
    public static $deletedIds = array();

    /** @var int */
    public $id;

    public function __construct($id)
    {
        $this->id = (int) $id;
    }

    /**
     * @return bool
     */
    public function delete()
    {
        self::$deletedIds[] = $this->id;

        return (bool) $this->deleteResult;
    }

    public static function reset()
    {
        self::$deletedIds = array();
    }
}

class PrestaShopCollection
{
    /** @var string */
    public $entity;

    /** @var array */
    public static $whereCalls = array();

    /** @var array */
    public static $items = array();

    /** @var array */
    public static $constructedEntities = array();

    public function __construct($entity)
    {
        $this->entity = $entity;
        self::$constructedEntities[] = $entity;
    }

    public static function reset()
    {
        self::$whereCalls = array();
        self::$items = array();
        self::$constructedEntities = array();
    }

    /**
     * @param string $field
     * @param string $operator
     * @param string $value
     */
    public function where($field, $operator, $value)
    {
        self::$whereCalls[] = array($field, $operator, $value);
    }

    /**
     * @return array
     */
    public function getAll()
    {
        return self::$items;
    }
}

class PrestaShopTestState
{
    public static function resetAll()
    {
        Module::reset();
        Configuration::reset();
        Tab::reset();
        Language::reset();
        Tools::reset();
        Link::reset();
        Smarty::reset();
        Controller::reset();
        Context::reset();
        Meta::reset();
        PrestaShopCollection::reset();
    }
}
