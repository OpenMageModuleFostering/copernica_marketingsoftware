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
class Copernica_MarketingSoftware_Helper_DataWriter
{
    /**
     *  Handle to a file that we can use to write REST requests 
     *  @var    resource
     */
    private $fileHandle = null;

    /**
     *  Current file that will be used to write data to files.
     *  @var    string
     */
    private $fileName = 'profiles.1.data';

    /**
     *  Path to working direcotry. Working directory will contain file with data.
     *  @var    string
     */ 
    private $workingDir = '';

    /**
     *  Constructor.
     */
    public function __construct()
    {
        // ensure that working directoru is valid. It will prepare working 
        // directory if there is none.
        $this->ensureWorkingDir();
        
        // we want to have a quick peak on what files are already in working dir,
        // and check from what file we should start
        $this->detectCurrentFile();

        // validate current data file and set proper current data file.
        $this->validateDataFile();

        // open handler to current data file
        $this->fileHandle = fopen($this->getFilePath(), 'a+');
    }

    /**
     *  Destructur.
     */
    public function __destruct()
    {
        if (!is_null($this->fileHandle)) fclose($this->fileHandle);
    }

    /**
     *  We want to detect current working file.
     */
    private function detectCurrentFile()
    {
        // open working directory
        $directoryHandle = opendir($this->workingDir);

        // data numbers
        $dataNumbers = array();

        while (false !== ($entry = readdir($directoryHandle)))
        {
            // if file starts with dot we want to skip it
            if (strpos($entry, '.') === 0) continue;

            $fileNameParts = explode('.', $entry);

            $dataNumbers[] = $fileNameParts[1];
        }

        // check if we have some infor about number of data file
        if (count($dataNumbers) > 0) 
        {
            // set filename
            $this->fileName = 'profiles.'.max($dataNumbers).'.data';
        }
    }

    /**
     *  Ensure that working directory is valid. This method will create working
     *  directory if there is none. 
     */
    private function ensureWorkingDir()
    {
        // get magento var dir. We will use it to store REST requests 
        $dir = Mage::getBaseDir('var');

        // check if we have a working dir
        if (!is_dir($dir.'/copernica_data')) {
            // create one if needed
            mkdir ($dir.'/copernica_data');
        }

        // store working directory
        $this->workingDir = $dir.'/copernica_data';
    }

    /**
     *  This method will check if we can write to current data file and will
     *  rotate files when they are too large.
     */
    private function validateDataFile() 
    {
        // 100mb in bytes. That would be our max size for one data file
        $mbInBytes = 104857600;

        // for debug we want to set lower amount of space as upper limit
        // $mbInBytes = 1024;

        // if we don't have a file then we want to create one
        if (!is_file($this->getFilePath())) $this->createDataFile($this->getFilePath());

        // check if current file is above 100mb
        if (filesize($this->getFilePath()) > $mbInBytes)
        {
            // get incremented file path
            $newFilePath = $this->getFilePath(+1);

            // create new data file
            $this->createDataFile($newFilePath);

            // store new file path
            $this->fileName = end(explode('/', $newFilePath));
        }
    }

    /**
     *  Create new data file at given path.
     *  @param  string
     */
    private function createDataFile($path)
    {
        if (is_file($path)) {
            // we have a file. Most likely we should handle such situation. 
            // we will see what we can do with it
        }
        else 
        {
            // this will create empty data file
            touch($path);
        }
    }

    /**
     *  Get file path to data file. Supplying null value as a parameter will 
     *  output current data file.
     *  @param  int     Increment number of data file from current file.
     *  @return string
     */
    private function getFilePath($inc = null) 
    {
        // get the current file name into local scope
        $fileName = $this->fileName;

        // we should increment file and return incremeneted file
        if (!is_null($inc)) 
        {
            // explode file name with dots
            $fileNameParts = explode('.', $this->fileName);

            // second part is the number of the data file. Increment it.
            $fileNameParts[1] += $inc;

            // implode with dots file name parts. That will give us new file name
            $fileName = implode('.', $fileNameParts);
        }

        // get the current file name
        else $fileName = $this->fileName;

        // return file path 
        return $this->workingDir.'/'.$fileName;
    }

    /**
     *  Write 
     *  @param  assoc   Array with data that we want to write
     */
    private function write($data)
    {
        // craete line of data
        $dataLine = json_encode($data);

        // write data to file
        fwrite($this->fileHandle, $dataLine.PHP_EOL);

        // validate data file
        $this->validateDataFile();
    }

    /**
     *  Clear all data files.
     */
    public function clearDataFiles()
    {
        // open working dir
        $directoryHandle = opendir($this->workingDir);

        // loop over every file in working dir and remove data files
        while (false !== ($entry = readdir($directoryHandle))) 
        {
            // if entry starts with a dot then that means it's a special file
            // that we should not touch.
            if (strpos($entry, '.') === 0) continue;

            // remove data file
            unlink($this->workingDir.'/'.$entry);
        }

        // close directory handle.
        closedir($directoryHandle);

        // we want to revalidate data file
        $this->validateDataFile();
    }

    /**
     *  Store customer profile.
     *  @param  assoc
     */
    public function storeProfile($profile)
    {
        // write profile data
        $this->write($profile);
    }
}