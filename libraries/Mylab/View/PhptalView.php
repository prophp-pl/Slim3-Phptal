<?php

namespace Mylab\View;

use Psr\Http\Message\ResponseInterface;
use PHPTAL;

/**
 * @copyright (c) 2015, MyLab (http://mylab.pl)
 * @version $Id: 1 2015-11-21 13:17:47 $;
 */
class PhptalView implements \ArrayAccess
{
    /**
     * PHPTAL engine
     *
     * @var PHPTAL
     */
    private $engine = null;

    /**
     * Config options
     * 
     * @var array
     */
    private $configDirectives = array(
        'templateRepository' => 'templates/',
        'compiledDir' => 'tmp/phptal/',
        'compiledFilesExtension' => 'php',
        'charset' => 'utf-8'
    );

    /**
     * Context variables
     *
     * @var array
     */
    private $variables = array();
    
    /**
     * Constructor
     *
     * @param array $config
     */
    public function __construct(array $config = [])
    {
        $this->engine = new PHPTAL();

        $this->loadConfig($config);
        $this->setCompiledDir();
        $this->setEncoding();
        $this->setTemplateRepository();
        $this->engine->setPhpCodeExtension($this->configDirectives['compiledFilesExtension']);
    }

    /**
     * Return the template engine object
     *
     * @return PHPTAL
     */
    public function getEngine()
    {
        return $this->engine;
    }
    
    /**
     * Returns character encoding for output
     *
     * @return string Character encoding
     */
    public function getEncoding()
    {
        return $this->engine->getEncoding();
    }

    /**
     * Set character encoding for output.
     * Must be executed before @see setContentType()
     *
     * @param string $encoding Character encoding
     * @return PhptalView
     */
    public function setEncoding($encoding = null)
    {
        $encoding = (is_null($encoding)) ? $this->configDirectives['charset'] : $encoding;
        $this->engine->setEncoding($encoding);
        return $this;
    }
    
    /**
     * 
     * @param mixed $repository
     * @return PhptalView
     */
    public function setTemplateRepository($repository = null)
    {
        $repository = (is_null($repository)) ? $this->configDirectives['templateRepository'] : $repository;
        $this->engine->setTemplateRepository($repository);
        return $this;
    }

    /**
     * Load config options
     *
     * @param array $config
     */
    private function loadConfig(array $config)
    {
        foreach ($config as $optk => $optv) {
            if (!array_key_exists($optk, $this->configDirectives)) {
                throw new \InvalidArgumentException("Configuration: Unknown option `$optk`");
            }
            $this->configDirectives["$optk"] = $optv;
        }
    }
    
    /**
     * Set directory for compiled files
     */
    private function setCompiledDir()
    {
        if (!is_dir($this->configDirectives['compiledDir'])) {
            throw new \InvalidArgumentException('Specified option `compiledDir` must be a directory');
        }
        if (!is_writable($this->configDirectives['compiledDir'])) {
            throw new \InvalidArgumentException('Compiled files directory must be writable');
        }
        $this->engine->setPhpCodeDestination($this->configDirectives['compiledDir']);
    }
    
    /**
     * Assign all variables to the PHPTAL engine
     *
     * @param array $variables Variables to assign
     */
    private function assignAll(array $variables = [])
    {
        foreach ($variables as $key => $value) {
            $this->engine->set($key, $value);
        }
    }

    /**
     * Fetch rendered template
     *
     * @param  string $template Template pathname relative to templates directory
     * @param  array  $data     Associative array of template variables
     *
     * @return string
     */
    public function fetch($template, $data = [])
    {
        $data = array_merge($this->variables, $data);
        $this->engine->setTemplate($template);
        $this->assignAll($data);

        return $this->engine->execute();
    }
    
    /**
     * Output rendered template
     *
     * @param ResponseInterface $response
     * @param  string $template Template pathname relative to templates directory
     * @param  array $data Associative array of template variables
     * @return ResponseInterface
     */
    public function render(ResponseInterface $response, $template, $data = [])
    {
         $response->getBody()->write($this->fetch($template, $data));

         return $response;
    }
    
    /**
     * Assign variables to the view
     * 
     * @param mixed $spec
     * @param mixed $value
     * @return PhptalView
     * @throws \InvalidArgumentException
     */
    public function assign($spec, $value = null)
    {
        if (is_string($spec)) {
            $this->variables[$spec] = $value;
        } elseif (is_array($spec)) {
            foreach ($spec as $key => $val) {
                $this->variables[$key] = $val;
            }
        } else {
            throw new \InvalidArgumentException('assign() expects a string or array, received ' . gettype($spec));
        }

        return $this;
    }
    
    /**
     * Get template variable
     *
     * @param string $key
     * @return null|mixed
     */
    public function __get($key)
    {
        if ($this->__isset($key)) {
            return $this->variables[$key];
        }
        return null;
    }
    
    /**
     * Set template variables
     *
     * @param string $key
     * @param mixed $value
     */
    public function __set($key, $value)
    {
        $this->assign($key, $value);
    }

    /**
     * Check if template variable is set
     *
     * @param string $key
     * @return bool
     */
    public function __isset($key)
    {
        return array_key_exists($key, $this->variables);
    }

    /**
     * Unset template variable
     *
     * @param string $key
     */
    public function __unset($key)
    {
        if ($this->__isset($key)) {
            unset($this->variables[$key]);
        }
    }

    /**
     * Does this collection have a given key?
     *
     * @param  string $key The data key
     *
     * @return bool
     */
    public function offsetExists($key)
    {
        return $this->__isset($key);
    }

    /**
     * Get collection item for key
     *
     * @param string $key The data key
     *
     * @return mixed The key's value, or the default value
     */
    public function offsetGet($key)
    {
        return $this->__get($key);
    }

    /**
     * Set collection item
     *
     * @param string $key   The data key
     * @param mixed  $value The data value
     */
    public function offsetSet($key, $value)
    {
        $this->__set($key, $value);
    }

    /**
     * Remove item from collection
     *
     * @param string $key The data key
     */
    public function offsetUnset($key)
    {
        $this->__unset($key);
    }
    
    /**
     * Get number of items in collection
     *
     * @return int
     */
    public function count()
    {
        return count($this->variables);
    }
    
    /**
     * Get collection iterator
     *
     * @return \ArrayIterator
     */
    public function getIterator()
    {
        return new \ArrayIterator($this->variables);
    }
}
