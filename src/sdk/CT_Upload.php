<?php
/**
 * CT_Upload 实现了一个简单的、可扩展的文件上传助手
 *
 * 使用方法：
 *
 * <code>
 * $allowExts = 'jpg,png,gif';
 * $maxSize = 150 * 1024; // 150KB
 * $uploadDir = dirname(__FILE__) . '/upload';
 *
 * $uploader = new CT_Upload();
 * $files = $uploader->getFiles();
 * foreach ($files as $file) {
 *     if (!$file->check($allowExts, $maxSize)) {
 *         // 上传的文件类型不符或者超过了大小限制。
 *         return false;
 *     }
 *     // 生成唯一的文件名（重复的可能性极小）
 *     $id = md5(time() . $file->getFilename() . $file->getSize() . $file->getTmpName());
 *     $filename = $id . '.' . strtolower($file->getExt());
 *     $file->move($uploadDir . '/' . $filename);
 * }
 * </code>
 */
class CT_Upload
{
    /**
     * 所有的 CT_Upload_File 对象实例
     *
     * @public array
     */
    public $_files = array();

    /**
     * 可用的上传文件对象数量
     *
     * @public int
     */
    public $_count;

    /**
     * 构造函数
     *
     * @param boolean $cascade
     *
     * @return upload
     */
    public function __construct($cascade = false)
    {
        if (is_array($_FILES)) {
            foreach ($_FILES as $field => $struct) {
                if (!isset($struct['error'])) { continue; }
                if (is_array($struct['error'])) {
                    $arr = array();
                    for ($i = 0; $i < count($struct['error']); $i++) {

                        if ($struct['error'][$i] != UPLOAD_ERR_NO_FILE) {
                            $arr[] = new CT_Upload_File($struct, $field, $i);
                            if (!$cascade) {
                                $this->_files["{$field}{$i}"] =& $arr[count($arr) - 1];
                            }
                        }
                    }
                    if ($cascade) {
                        $this->_files[$field] = $arr;
                    }
                } else {
                    if ($struct['error'] != UPLOAD_ERR_NO_FILE) {
                        $this->_files[$field] =  new CT_Upload_File($struct, $field);
                    }
                }
            }
        }
        $this->_count = count($this->_files);
    }

    /**
     * 可用的上传文件对象数量
     *
     * @return int
     */
    public function getCount()
    {
        return $this->_count;
    }

    /**
     * 返回所有的上传文件对象
     *
     * @return array
     */
    public function getFiles()
    {
        return $this->_files;
    }

    /**
     * 检查指定名字的上传文件对象是否存在
     *
     * @param string $name
     *
     * @return boolean
     */
    public function existsFile($name)
    {
        return isset($this->_files[$name]);
    }

    /**
     * 返回指定名字的上传文件对象
     *
     * @param string $name
     *
     * @return upload_File
     */
    public function getFile($name)
    {
        if (!isset($this->_files[$name])) {
            FLEA::loadClass('FLEA_Exception_ExpectedFile');
            return __THROW(new FLEA_Exception_ExpectedFile('$_FILES[' . $name . ']'));
        }
        return $this->_files[$name];
    }

    /**
     * 检查指定的上传文件是否存在
     *
     * @param string $name
     *
     * @return boolean
     */
    public function isFileExist($name)
    {
        return isset($this->_files[$name]);
    }

    /**
     * 批量移动上传的文件到目标目录
     *
     * @param string $destDir
     */
    public function moveAll($destDir)
    {
        foreach ($this->_files as $file) {
            /* @public $file upload_File */
            $file->move($destDir . '/' . $file->getFilename());
        }
    }
}

/**
 * 封装一个上传的文件
 *
 */
class CT_Upload_File
{
    /**
     * 上传文件信息
     *
     * @public array
     */
    public $_file = array();

    /**
     * 上传文件对象的名字
     *
     * @public string
     */
    public $_name;

    /**
     * 构造函数
     *
     * @param array $struct
     * @param string $name
     * @param int $ix
     *
     * @return upload_File
     */
    public function __construct($struct, $name, $ix = false)
    {
        if ($ix !== false) {
            $s = array(
                'name' => $struct['name'][$ix],
                'type' => $struct['type'][$ix],
                'tmp_name' => $struct['tmp_name'][$ix],
                'error' => $struct['error'][$ix],
                'size' => $struct['size'][$ix],
            );
            $this->_file = $s;
        } else {
            $this->_file = $struct;
        }

        $this->_file['is_moved'] = false;
        $this->_name = $name;
    }

    /**
     * 设置自定义属性
     *
     * @param string $name
     * @param mixed $value
     */
    public function setAttribute($name, $value)
    {
        $this->_file[$name] = $value;
    }

    /**
     * 获取自定义属性
     *
     * @param string $name
     *
     * @return mixed
     */
    public function getAttribute($name)
    {
        return $this->_file[$name];
    }

    /**
     * 返回上传文件对象的名字
     *
     * @return string
     */
    public function getName()
    {
        return $this->_name;
    }

    /**
     * 指示上传是否成功
     *
     * @return boolean
     */
    public function isSuccessed()
    {
        return $this->_file['error'] == UPLOAD_ERR_OK;
    }

    /**
     * 返回上传错误代码
     *
     * @return int
     */
    public function getError()
    {
        return $this->_file['error'];
    }

    /**
     * 指示上传文件是否已经从临时目录移出
     *
     * @return boolean
     */
    public function isMoved()
    {
        return $this->_file['is_moved'];
    }

    /**
     * 返回上传文件的原名
     *
     * @return string
     */
    public function getFilename()
    {
        return $this->_file['name'];
    }

    /**
     * 返回上传文件不带"."的扩展名
     *
     * @return string
     */
    public function getExt()
    {
        if ($this->isMoved()) {
            return pathinfo($this->getNewPath(), PATHINFO_EXTENSION);
        } else {
            return pathinfo($this->getFilename(), PATHINFO_EXTENSION);
        }
    }

    /**
     * 返回上传文件的大小（字节数）
     *
     * @return int
     */
    public function getSize()
    {
        return $this->_file['size'];
    }

    /**
     * 返回上传文件的 MIME 类型（由浏览器提供，不可信）
     *
     * @return string
     */
    public function getMimeType()
    {
        return $this->_file['type'];
    }

    /**
     * 返回上传文件的临时文件名
     *
     * @return string
     */
    public function getTmpName()
    {
        return $this->_file['tmp_name'];
    }

    /**
     * 获得文件的新路径（通常是移动后的新路径，包括文件名）
     *
     * @return string
     */
    public function getNewPath()
    {
        return $this->_file['new_path'];
    }

    /**
     * 检查上传的文件是否成功上传，并符合检查条件（文件类型、最大尺寸）
     *
     * 文件类型以扩展名为准，多个扩展名以 , 分割，例如 .jpg,.jpeg,.png。
     *
     * @param string $allowExts 允许的扩展名
     * @param int $maxSize 允许的最大上传字节数
     *
     * @return boolean
     */
    public function check($allowExts = null, $maxSize = null)
    {
        if (!$this->isSuccessed()) { return false; }

        if ($allowExts && $allowExts!='*') {
            if (strpos($allowExts, ',')) {
                $exts = explode(',', $allowExts);
            } elseif (strpos($allowExts, '/')) {
                $exts = explode('/', $allowExts);
            } elseif (strpos($allowExts, '|')) {
                $exts = explode('|', $allowExts);
            } else {
                $exts = array($allowExts);
            }

            $filename = $this->getFilename();
            $fileexts = explode('.', $filename);
            array_shift($fileexts);
            $count = count($fileexts);
            $passed = false;
            $exts = array_filter(array_map('trim', $exts), 'trim');
            foreach ($exts as $ext) {
                if (substr($ext, 0, 1) == '.') {
                    $ext = substr($ext, 1);
                }
                $fileExt = implode('.', array_slice($fileexts, $count - count(explode('.', $ext))));
                if (strtolower($fileExt) == strtolower($ext)) {
                    $passed = true;
                    break;
                }
            }
            if (!$passed) {
                return false;
            }
        }
        $maxSize = (int)$maxSize * 1024;
        if ($maxSize && $this->getSize() > $maxSize) {
            return false;
        }

        return true;
    }

    /**
     * 移动上传文件到指定位置和文件名
     *
     * @param string $destPath
     */
    public function move($destPath)
    {
        $this->_file['is_moved'] = true;
        $this->_file['new_path'] = $destPath;
        return move_uploaded_file($this->_file['tmp_name'], $destPath);
    }

    /**
     * 删除上传的文件
     */
    public function remove()
    {
        if ($this->isMoved()) {
            unlink($this->getNewPath());
        } else {
            unlink($this->getTmpName());
        }
    }

    /**
     * 删除移动后的文件
     */
    public function removeMovedFile()
    {
        if ($this->isMoved()) {
            unlink($this->getNewPath());
        }
    }
}
