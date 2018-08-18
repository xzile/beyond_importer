<?php


namespace App\Services;

use GuzzleHttp\Client;

class Character
{
    private $data;
    /** @var Config */
    private $config;

    public function __construct()
    {
        /** @var Config config */
        $this->config = app()->make(Config::class);
    }

    /**
     * @return mixed
     */
    public function getData()
    {
        return $this->data;
    }

    /**
     * @param mixed $data
     */
    public function setData($data): void
    {
        $this->data = $data;
    }


    /**
     * @param $name
     * @return mixed|null
     */
    public function __get($name)
    {
        if (method_exists($this, $name)) {
            return $this->{$name}();
        } else {
            return null;
        }
    }


    private function name()
    {
        return $this->data->name;
    }

    private function gender()
    {
        return $this->data->gender;
    }

    private function age()
    {
        return $this->data->age;
    }

    private function alignment()
    {
        // @todo Convert to proper alignment, as alignmentId is an integer
        return $this->data->alignmentId;
    }

    private function race()
    {
        return $this->data->race->fullName;
    }

    private function size()
    {
        return $this->data->race->size;
    }

    private function background()
    {
        return $this->data->background->definition->name;
    }

    private function level()
    {
        $level = 0;
        foreach($this->data->classes as $class){
            $level += $class->level;
        }
        return $level;
    }
}
