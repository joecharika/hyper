<?php


namespace Hyper\Application\Controllers {


    use Hyper\{Application\HyperApp, Functions\Str, Models\User, SQL\Database\DatabaseContext};

    trait TControllerContext
    {
        protected ?DatabaseContext $db;

        public ?HyperApp $app;

        public ?User $user;

        public ?string
            $modelName,
            $name,
            $model;

        public function __construct()
        {
            $this->app = HyperApp::instance();

            $names = explode('\\', static::class);
            $this->name = $this->name ?? strtr($names[array_key_last($names)], ['Controller' => '']);
            $this->model = $this->model ?? '\\Models\\' . Str::singular($this->name);
            $this->modelName = $this->modelName ?? Str::singular($this->name);

            if (class_exists($this->model))
                $this->db = new DatabaseContext($this->modelName);

            $this->user = HyperApp::$user;
        }

    }
}