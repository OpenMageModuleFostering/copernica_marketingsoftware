<?php
/**
 * Copernica Marketing Software 
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0).
 * It is available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 * If you are unable to obtain a copy of the license through the 
 * world-wide-web, please send an email to copernica@support.cream.nl 
 * so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade this software 
 * to newer versions in the future. If you wish to customize this module 
 * for your needs please refer to http://www.magento.com/ for more 
 * information.
 *
 * @category     Copernica
 * @package      Copernica_MarketingSoftware
 * @copyright    Copyright (c) 2011-2012 Copernica & Cream. (http://docs.cream.nl/)
 * @license      http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 *  This helper will help us with writing data into set of files that can be 
 *  processed by Copernica employes.
 */
class Copernica_MarketingSoftware_Helper_Datawriter
{
    /**
     *  Handle to a file that we can use to write REST requests
     *  
     *  @var    resource
     */
    protected $_fileHandle = null;

    /**
     *  Current file that will be used to write data to files.
     *  
     *  @var    string
     */
    protected $_fileName = 'profiles.1.data';

    /**
     *  Path to working direcotry. Working directory will contain file with data.
     *  
     *  @var    string
     */ 
    protected $_workingDir = '';

    /**
     *  Constructor.
     */
    public function __construct()
    {
        $this->_ensureWorkingDir();

        $this->_detectCurrentFile();

        $this->_validateDataFile();

        $this->_fileHandle = fopen($this->_getFilePath(), 'a+');
    }

    /**
     *  Destructor.
     */
    public function __destruct()
    {
        if (!is_null($this->_fileHandle)) {
        	fclose($this->_fileHandle);
        }
    }

    /**
     *  We want to detect current working file.
     */
    protected function _detectCurrentFile()
    {
        $directoryHandle = opendir($this->_workingDir);

        $dataNumbers = array();

        while (false !== ($entry = readdir($directoryHandle))) {
            if (strpos($entry, '.') === 0) {
            	continue;
            }

            $fileNameParts = explode('.', $entry);

            $dataNumbers[] = $fileNameParts[1];
        }

        if (count($dataNumbers) > 0) {
            $this->_fileName = 'profiles.'.max($dataNumbers).'.data';
        }
    }

    /**
     *  Ensure that working directory is valid. This method will create working
     *  directory if there is none. 
     */
    protected function _ensureWorkingDir()
    { 
        $dir = Mage::getBaseDir('var');

        if (!is_dir($dir.'/copernica_data')) {
            mkdir ($dir.'/copernica_data');
        }

        $this->_workingDir = $dir.'/copernica_data';
    }

    /**
     *  This method will check if we can write to current data file and will
     *  rotate files when they are too large.
     */
    protected function _validateDataFile() 
    {
        // 100mb in bytes. That would be our max size for one data file
        $mbInBytes = 104857600;

        if (!is_file($this->_getFilePath())) {
        	$this->_createDataFile($this->_getFilePath());
        }

        if (filesize($this->_getFilePath()) > $mbInBytes) {
            $newFilePath = $this->_getFilePath(+1);

            $this->_createDataFile($newFilePath);

            $this->_fileName = end(explode('/', $newFilePath));
        }
    }

    /**
     *  Create new data file at given path.
     *  
     *  @param	string	$path
     */
    protected function _createDataFile($path)
    {
        if (is_file($path)) {
            // we have a file. Most likely we should handle such situation. 
            // we will see what we can do with it
        } else {
            touch($path);
        }
    }

    /**
     *  Get file path to data file. Supplying null value as a parameter will 
     *  output current data file.
     *  
     *  @param	int	$inc
     *  @return	string
     */
    protected function _getFilePath($inc = null) 
    {
        $fileName = $this->_fileName;

        if (!is_null($inc)) {
            $fileNameParts = explode('.', $this->_fileName);
            $fileNameParts[1] += $inc;

            $fileName = implode('.', $fileNameParts);
        } else {
        	$fileName = $this->_fileName;
        }
 
        return $this->_workingDir.'/'.$fileName;
    }

    /**
     *  Write 
     *  
     *  @param	assoc	$data
     */
    protected function _write($data)
    {
        $dataLine = json_encode($data);

        fwrite($this->_fileHandle, $dataLine.PHP_EOL);

        $this->_validateDataFile();
    }

    /**
     *  Clear all data files.
     */
    public function clearDataFiles()
    {
        $directoryHandle = opendir($this->_workingDir);

        while (false !== ($entry = readdir($directoryHandle))) {
            if (strpos($entry, '.') === 0) {
            	continue;
            }

            unlink($this->_workingDir.'/'.$entry);
        }

        closedir($directoryHandle);

        $this->_validateDataFile();
    }

    /**
     *  Store customer profile.
     *  
     *  @param	assoc	$profile
     */
    public function storeProfile($profile)
    {
        $this->_write($profile);
    }
}