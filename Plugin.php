<?php
if (!defined('__TYPECHO_ROOT_DIR__')) exit;
/**
 * AdsPlugin 广告编辑器
 * 
 * @package AdsPlugin 
 * @author ScientistPun
 * @version 1.0.0
 * @link https://www.koalilab.top
 */
class AdsPlugin_Plugin implements Typecho_Plugin_Interface
{
    /**
     * 激活插件方法,如果激活失败,直接抛出异常
     * 
     * @access public
     * @return void
     * @throws Typecho_Plugin_Exception
     */
    public static function activate()
    {
        $info = AdsPlugin_Plugin::install();
        Helper::addPanel(1, 'AdsPlugin/manage-ads.php', '广告位', '管理广告位', 'administrator');
        Helper::addAction('ads-edit', 'AdsPlugin_Action');
        return _t($info);
    }

    // 安装方法
    public static function install()
    {
        // 安装数据库
        $installDb = Typecho_Db::get();
        $type = explode('_', $installDb->getAdapterName());
        $type = array_pop($type);
        $prefix = $installDb->getPrefix();
        $scripts = file_get_contents('usr/plugins/AdsPlugin/' . $type . '.sql');
        $scripts = str_replace('typecho_', $prefix, $scripts);
        $scripts = str_replace('%charset%', 'utf8', $scripts);
        $scripts = explode(';', $scripts);
        try {
            foreach ($scripts as $script) {
                $script = trim($script);
                if ($script) {
                    $installDb->query($script, Typecho_Db::WRITE);
                }
            }
            return '建立广告位数据表，广告位插件启用成功';
        } catch (Exception $e) {
            print_r($e);
            $code = $e->getCode();

            if (('Mysql' == $type || 1050 == $code) ||
                ('SQLite' == $type && ('HY000' == $code || 1 == $code))
            ) {
                try {
                    $script = 'SELECT `aid`, `keyword`, `type`, `name`, `content`, `description`, `width`, `height`, `status` from `' . $prefix . 'links`';
                    $installDb->query($script, Typecho_Db::READ);
                    return '检测到广告位数据表，广告位插件启用成功';
                } catch (Typecho_Db_Exception $e) {
                    $code = $e->getCode();
                    throw new Typecho_Plugin_Exception('数据表检测失败，广告位插件启用失败。错误号：' . $code);
                }
            } else {
                throw new Typecho_Plugin_Exception('数据表建立失败，广告位插件启用失败。错误号：' . $code);
            }
        }
    }

    /**
     * 禁用插件方法,如果禁用失败,直接抛出异常
     * 
     * @static
     * @access public
     * @return void
     * @throws Typecho_Plugin_Exception
     */
    public static function deactivate()
    {
        $msg = self::uninstall();
        return $msg . '插件卸载成功';
    }

    public static function uninstall()
    {
        //删除路由
        Helper::removePanel(1, 'AdsPlugin/manage-ads.php');
        Helper::removeAction('ads-edit');
        //获取配置，是否删除数据表
        if (Helper::options()->plugin('AdsPlugin')->delete == 1) {
            return self::remove_table();
        }
    }

    public static function remove_table()
    {
        //删除表
        $db = Typecho_Db::get();
        $prefix = $db->getPrefix();
        try {
            $db->query("DROP TABLE `" . $prefix . "ads`", Typecho_Db::WRITE);
        } catch (Typecho_Exception $e) {
            return "删除广告位表失败！";
        }
        return "删除广告位表成功！";
    }

    /**
     * 获取插件配置面板
     * 
     * @access public
     * @param Typecho_Widget_Helper_Form $form 配置面板
     * @return void
     */
    public static function config(Typecho_Widget_Helper_Form $form)
    {
        /** 分类名称 */
        $element = new Typecho_Widget_Helper_Form_Element_Radio('delete', array(0 => '不删除', 1 => '删除'), 0, _t('卸载是否删除数据表'));
        $form->addInput($element);
    }


    public static function form($action = NULL)
    {
        /** 构建表格 */
        $options = Typecho_Widget::widget('Widget_Options');
        $form = new Typecho_Widget_Helper_Form(
            Typecho_Common::url('/action/ads-edit', $options->index),
            Typecho_Widget_Helper_Form::POST_METHOD
        );
        /** 广告位关键字 */
        $keyword = new Typecho_Widget_Helper_Form_Element_Text('keyword', NULL, NULL, _t('广告位关键字'), _t('只能以英文字母和下划线组成的字符串'));
        $form->addInput($keyword);

        /** 广告位名称 */
        $name = new Typecho_Widget_Helper_Form_Element_Text('name', NULL, NULL, _t('广告位名称*'));
        $form->addInput($name);

        /** 广告位展示类型 */
        $type = new Typecho_Widget_Helper_Form_Element_Select('type', [_t('图片展示'), _t('代码块'), _t('轮播图')], 0, _t('广告位展示'));
        $form->addInput($type);

        /** 广告位内容 */
        $content =  new Typecho_Widget_Helper_Form_Element_Textarea('content', NULL, NULL, _t('广告位内容'));
        $form->addInput($content);

        /** 广告位描述 */
        $description =  new Typecho_Widget_Helper_Form_Element_Textarea('description', NULL, NULL, _t('广告位描述'));
        $form->addInput($description);

        /** 尺寸 */
        $width = new Typecho_Widget_Helper_Form_Element_Text('width', NULL, NULL, _t('宽度'), _t('广告位宽度，0则自适应'));
        $form->addInput($width);
        $height = new Typecho_Widget_Helper_Form_Element_Text('height', NULL, NULL, _t('高度'), _t('广告位高度，0则自适应'));
        $form->addInput($height);

        /** 状态 */
        $status = new Typecho_Widget_Helper_Form_Element_Radio('status', ['禁用', '启用'], 1, _t('状态'), _t('广告位启动状态'));
        $form->addInput($status);

        /** 更新时间 */
        $updatetime = new Typecho_Widget_Helper_Form_Element_Hidden('updatetime');
        $form->addInput($updatetime);

        /** 广告位动作 */
        $do = new Typecho_Widget_Helper_Form_Element_Hidden('do');
        $form->addInput($do);

        /** 广告位主键 */
        $aid = new Typecho_Widget_Helper_Form_Element_Hidden('aid');
        $form->addInput($aid);

        /** 提交按钮 */
        $submit = new Typecho_Widget_Helper_Form_Element_Submit();
        $submit->input->setAttribute('class', 'btn primary');
        $form->addItem($submit);
        $request = Typecho_Request::getInstance();

        if (isset($request->aid) && 'insert' != $action) {
            /** 更新模式 */
            $db = Typecho_Db::get();
            $prefix = $db->getPrefix();
            $ad = $db->fetchRow($db->select()->from($prefix . 'ads')->where('aid = ?', $request->aid));
            if (!$ad) {
                throw new Typecho_Widget_Exception(_t('广告位不存在'), 404);
            }

            $keyword->value($ad['keyword']);
            $name->value($ad['name']);
            $type->value($ad['type']);
            $content->value($ad['content']);
            $description->value($ad['description']);
            $width->value($ad['width']);
            $height->value($ad['height']);
            $status->value($ad['status']);
            $do->value('update');
            $aid->value($ad['aid']);
            $submit->value(_t('编辑广告位'));
            $_action = 'update';
        } else {
            $do->value('insert');
            $submit->value(_t('增加广告位'));
            $_action = 'insert';
        }

        if (empty($action)) {
            $action = $_action;
        }

        /** 给表单增加规则 */
        if ('insert' == $action || 'update' == $action) {
            $keyword->addRule('required', _t('必须填写关键字'));
            $name->addRule('required', _t('必须填写名称'));
            $width->addRule('isInteger', _t('请填写数字'));
            $height->addRule('isInteger', _t('请填写数字'));
        }
        if ('update' == $action) {
            $keyword->input->setAttribute('readonly', true);
            $aid->addRule('required', _t('广告位不存在'));
            $aid->addRule(array(new adsPlugin_Plugin, 'isExists'), _t('广告位不存在'));
        }
        return $form;
    }

    /**
     * 查看是否存在记录
     * @access public
     * @param int $aid
     * @return bool
     */
    public static function isExists($aid)
    {
        $db = Typecho_Db::get();
        $prefix = $db->getPrefix();
        $link = $db->fetchRow($db->select()->from($prefix . 'ads')->where('aid = ?', $aid)->limit(1));
        return $link ? true : false;
    }

    /**
     * 个人用户的配置面板
     * 
     * @access public
     * @param Typecho_Widget_Helper_Form $form
     * @return void
     */
    public static function personalConfig(Typecho_Widget_Helper_Form $form)
    {
    }

    /**
     * 插件实现方法
     * 
     * @access public
     * @return void
     */
    public static function render()
    {
        echo '<span class="message success">'
            . htmlspecialchars(Typecho_Widget::widget('Widget_Options')->plugin('AdsPlugin')->word)
            . '</span>';
    }


    /**
     * 根据关键字输出广告位
     */
    public static function output($keyword)
    {
        $options = Typecho_Widget::widget('Widget_Options');
        if (!isset($options->plugins['activated']['AdsPlugin'])) {
            return _t('广告位插件未激活');
        }
        if (!$keyword) {
            return _t('未找到相关的广告位');
        }

        /** 取出数据 */
        $db = Typecho_Db::get();
        $prefix = $db->getPrefix();
        $options = Typecho_Widget::widget('Widget_Options');
        $ad = $db->fetchRow($db->select()->from($prefix . 'ads')->where("keyword = '{$keyword}'")->limit(1));
        if (!$ad) {
            return _t('未找到相关的广告位');
        }
        if ($ad['status'] == 0) {
            return _t('该广告位未启用');
        }

        require_once dirname(__FILE__) . '/Ads.php';
        echo Ads::instance(['type' => $ad['type'], 'content' => $ad['content'], 'width' => $ad['width'], 'height' => $ad['height']])
            ->show();
        return true;
    }
}
