<?php


class VCache {

    protected $path;
    protected $cache;
    protected $debug = false;
    protected $messages = array();
    protected $autoclean = false;

    public function __construct($path = null, $timezone) {
    	/*setting time zone
    	 * Edit the time zone according to yours*/
    	 if($timezone){
    		date_default_timezone_set('Europe/Paris');
    	 }else{
    	 	date_default_timezone_set($timezone);
    	 }

        if (is_dir($path) && is_writable($path)) {
            $path = str_replace('\\', '/', $path);
            $path = (substr($path, -1, 1) == '/') ? $path : $path . '/';
            $this->path = $path;
            $this->showmsg('The script was successfully configured, your working path is: ' . $path, 2);
        } else {
            mkdir($path);
            $path = str_replace('\\', '/', $path);
            $path = (substr($path, -1, 1) == '/') ? $path : $path . '/';
            $this->path = $path;
            $this->showmsg('The script was successfully configured, your working path is: ' . $path, 2);
        }
    }

    public function write($name, $data, $expire = null) {
    	if(!$expire){
    		//default lifetime for each cache
			$expire = '+15 minutes';
    	}

        if ($this->autoclean == true) {
            $this->clean();
        }

        if (empty($name) || empty($data)) {
            $this->showmsg('You are required to enter a valid name for the cache and a valid data to be cached.', 1);
            return false;
        } else {
            $cache['filename'] = md5($name);
            $cache['data'] = base64_encode(serialize($data));
            $cache['expire'] = strtotime($expire);
            $this->cache = $cache;
            //writing files to cache
            $caching = $this->putContents();
            if ($caching == true) {
                $this->showmsg($name . ' have been successfully cached.', 2);
                return true;
            } else {
                $this->showmsg('Something went wrong when the script tryed to cache ' . $name, 1);
                return false;
            }
        }
    }

    public function get($name) {
        if (!empty($name)) {
            $data = $this->getContents(md5($name));
            if ($data != false) {
                $data = explode('::', $data);
                if ($data[0] >= date('Y-m-d H:i:s')) {
                    $this->showmsg($name . ' was successfully returned from the cache.', 2, $data[1]);
                    return unserialize(base64_decode($data[1]));
                } else {
                    if (file_exists($this->path . $name . '.cache')) {
                        $this->showmsg($name . ' has expired, the file ' . $this->path . md5($name) . '.cache' . ' will be deleted now.', 4);
                        unlink($this->path . md5($name) . '.cache');
                    }
                    $this->showmsg($name . ' has expired.', 4);
                    return false;
                }
            } else {
                $this->showmsg('Something went wrong wen the script tried to get the cache ' . $name, 1);
                return false;
            }
        } else {
            $this->showmsg('The name parameter can\'t be empty.', 1);
            return false;
        }
    }

    protected function putContents() {
        if (!file_exists($this->path . $this->cache['filename'] . '.cache')) {
            file_put_contents($this->path . $this->cache['filename'] . '.cache', $this->cache['expire'] . '::' . $this->cache['data']);
            $this->showmsg($this->cache['filename'] . ' was succefully cached', 2, $this->cache['data']);
            return true;
        } else {
            $this->showmsg('The file ' . $this->path . $this->cache['filename'] . '.cache' . 'already exists, skiping cache.', 4, $this->cache['data']);
            return false;
        }
    }

    protected function getContents($name) {
        if (file_exists($this->path . $name . '.cache')) {
            $this->showmsg('The file ' . $this->path . $name . '.cache' . ' have been read successfully.', 2);
            return file_get_contents($this->path . $name . '.cache');
        } else {
            $this->showmsg('The file ' . $this->path . $name . '.cache' . ' doesn\'t exists, skiping.', 4);
            return false;
        }
    }

    public function delete($name) {
        if (file_exists($this->path . md5($name) . '.cache')) {
            $this->showmsg('The file ' . $this->path . md5($name) . '.cache' . ' have been deleted successfully.', 2);
            unlink($this->path . md5($name) . '.cache');
            return true;
        } else {
            $this->showmsg('The file ' . $this->path . md5($name) . '.cache' . ' doesn\'t exists, skiping.', 4);
            return false;
        }
    }

    public function purge() {
        $files = array_slice(scandir($this->path), 2);
        if (!empty($files)) {
            foreach ($files as $file) {
                if (file_exists($this->path . $file) && stripos('.cache', $file) != false) {
                    $this->showmsg('The file ' . $this->path . $file . ' have been deleted successfully.', 2);
                    unlink($this->path . $file);
                }
            }
        } else {
            $this->showmsg('There is no files to be erased in the work path.', 4);
        }
    }

    public function clean() {
        $files = array_slice(scandir($this->path), 2);
        if (!empty($files)) {
            foreach ($files as $file) {
                if (file_exists($this->path . $file) && stripos('.cache', $file) != false) {
                    $data = file_get_contents($this->path . $file);
                    $data = explode('::', $data);
                    if ($data[0] <= date('Y-m-d H:i:s')) {
                        $this->showmsg('The file ' . $this->path . $file . ' have been cleaned successfully.', 2);
                        unlink($this->path . $file);
                    }
                }
            }
        } else {
            $this->showmsg('There is no files to be cleaned in the working path.', 4);
        }
    }

    public function debug() {
    	$this->debug = true;
        return;
    }

    public function showmsg($msg) {
    	if($this->debug){
			echo $msg.'<br/>';
    	}
        return;
    }
}

?>
