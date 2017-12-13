<?php
/**
 * EIU PHP FRAMEWORK
 *
 * @author        成都东联智胜软件有限公司
 * @link          https://www.cneiu.com
 */


namespace eiu\core\service\view;


use eiu\core\service\config\ConfigProvider;
use eiu\core\service\output\OutputProvider;
use eiu\core\service\Provider;


/**
 * Class ViewProvider
 *
 * @package eiu\core\service\event
 */
class ViewProvider extends Provider
{
    /**
     * @var OutputProvider
     */
    private $output;
    
    /**
     * @var string
     */
    private $charset;
    
    /**
     * 模板变量
     *
     * @var array
     */
    private $_template_vars = [];
    
    /**
     * 服务启动
     *
     * @param ConfigProvider $config
     */
    public function boot(ConfigProvider $config)
    {
        $this->charset = $config['app']['CHARSET'] ?? 'utf8';
    }
    
    /**
     * 读取模板变量
     *
     * @param string $index 变量名
     *
     * @return mixed
     */
    public function __get(string $index)
    {
        return $this->_template_vars[$index];
    }
    
    /**
     * 设置模板变量
     *
     * @param string $index 变量名
     *
     * @param        mixed
     */
    public function __set(string $index, $value)
    {
        $this->_template_vars[$index] = $value;
    }
    
    /**
     * 显示视图
     *
     * 显示一个视图模板
     *
     * @param string $page        模板文件
     * @param bool   $return      是否返回视图, 返回后不会输出
     * @param int    $status_code 状态代码
     * @param string $header_type 输出类型
     * @param string $charset     字符集
     *
     * @return string
     * @throws \Exception
     */
    public function display(string $page, bool $return = false, int $status_code = 200, string $header_type = 'html', string $charset = null)
    {
        $text = '';
        
        if (!is_file($page = $this->getPath($page)))
        {
            throw new \Exception("Template file $page does not exist.");
        }
        
        $html = (new TemplateEngine($this->app, $this->_template_vars))->render($page);
        
        $this->_template_vars = [];
        
        if ($return)
        {
            return $html;
        }
        else
        {
            $this->output = $this->app->make(OutputProvider::class);
            $this->output->setOutput($html ?: '');
        }
        
        return $this->text($text, $status_code, $header_type, $charset);
    }
    
    /**
     * 检查模板是否存在
     *
     * @param   string $page 模板文件
     *
     * @return bool|string
     */
    public function getPath(string $page)
    {
        if (!$page)
        {
            return false;
        }
        
        $page = str_replace('/', DS, $page);
        $page = 0 === stripos($page, APP_TEMPLATE) ? $page : APP_TEMPLATE . $page;
        $page .= (pathinfo($page, PATHINFO_EXTENSION) ? '' : '.tpl.php');
        
        return $page;
    }
    
    /**
     * 显示文本
     *
     * 显示一个视图文本
     *
     * @param string $text        输出文本
     * @param int    $status_code 状态代码
     * @param string $header_type 输出类型
     * @param string $charset     字符集
     *
     * @return string
     */
    public function text($text, int $status_code = 200, string $header_type = 'html', string $charset = null)
    {
        $this->output = $this->app->make(OutputProvider::class);
        $this->output->setOutput($text);
        $this->output->setHeader('Access-Control-Allow-Origin: ' . $this->app->request->header('Origin') ?: '*');
        $this->output->setHeader('Access-Control-Allow-Credentials: true');
        $this->output->setHeader('Access-Control-Allow-Methods: GET, POST, OPTIONS, PUT, PATCH, DELETE');
        $this->output->setHeader('Access-Control-Allow-Headers: Origin, X-Requested-With, Content-Type, Accept, Authorization');
        $this->output->setHeaderStatus($status_code);
        $this->output->setHeaderType($header_type);
        $this->output->setHeaderCharset($charset ?: $this->charset);
        
        return $text;
    }
    
    /**
     * 检查模板是否存在
     *
     * @param   string $page 模板文件
     *
     * @return bool|string
     */
    public function exist(string $page)
    {
        if (!$page)
        {
            return false;
        }
        
        $page = str_replace('/', DS, $page);
        $page = 0 === stripos($page, APP_TEMPLATE) ? $page : APP_TEMPLATE . $page;
        $page .= (pathinfo($page, PATHINFO_EXTENSION) ? '' : '.tpl.php');
        
        return is_file($page) ? $page : false;
    }
}