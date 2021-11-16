<?php

namespace php6140\tool;

class ReadDir
{
    public array $files = [];

    function add(string ...$pathStr): self
    {
        $dir = implode(DIRECTORY_SEPARATOR, $pathStr);
        $handle = opendir($dir);
        if ($handle) {
            while (($fl = readdir($handle)) !== false) {
                $path = $dir . DIRECTORY_SEPARATOR . $fl;
                if ($fl != '.' && $fl != '..') {
                    if (is_dir($path)) {
                        $this->add($path);
                    } else {
                        $name = strtolower($fl);
                        $this->files[$name] = $path;
                    }
                }
            }
        }
        closedir($handle);
        return $this;
    }

    function set($name, $val): self
    {
        $this->files[$name] = $val;
        return $this;
    }

    function del($name): self
    {
        unset($this->files[$name]);
        return $this;
    }
}