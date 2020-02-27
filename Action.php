<?php
class AdsPlugin_Action extends Typecho_Widget implements Widget_Interface_Do
{
	private $db;
	private $options;
	private $prefix;
			
	public function insert()
	{
		if (AdsPlugin_Plugin::form('insert')->validate()) {
			$this->response->goBack();
		}
		/** 取出数据 */
		$ad = $this->request->from('aid', 'keyword', 'type', 'name', 'content', 'description', 'width', 'height', 'status', 'updatetime');

		/** 插入数据 */
		$ad['aid'] = $this->db->query($this->db->insert($this->prefix.'ads')->rows($ad));

		/** 设置高亮 */
		$this->widget('Widget_Notice')->highlight('ads-'.$ad['aid']);

		/** 提示信息 */
		$this->widget('Widget_Notice')->set(_t('广告位 <a href="%s">%s</a> 已经被增加', NULL, $ad['keyword']), NULL, 'success');

		/** 转向原页 */
		$this->response->redirect(Typecho_Common::url('extending.php?panel=AdsPlugin%2Fmanage-ads.php', $this->options->adminUrl));
	}

	public function update()
	{
		if (AdsPlugin_Plugin::form('update')->validate()) {
			$this->response->goBack();
		}

		/** 取出数据 */
		$ad = $this->request->from('aid', 'keyword', 'type', 'name', 'content', 'description', 'width', 'height', 'status', 'updatetime');

		/** 更新数据 */
		$this->db->query($this->db->update($this->prefix.'ads')->rows($ad)->where('aid = ?', $ad['aid']));

		/** 设置高亮 */
		$this->widget('Widget_Notice')->highlight('ads-'.$ad['aid']);

		/** 提示信息 */
		$this->widget('Widget_Notice')->set(_t('广告位 <a href="%s">%s</a> 已经被更新', NULL, $ad['keyword']), NULL, 'success');

		/** 转向原页 */
		$this->response->redirect(Typecho_Common::url('extending.php?panel=AdsPlugin%2Fmanage-ads.php', $this->options->adminUrl));
	}

    public function delete()
    {
        $aids = $this->request->filter('int')->getArray('aid');
        $deleteCount = 0;
        if ($aids && is_array($aids)) {
            foreach ($aids as $aid) {
                if ($this->db->query($this->db->delete($this->prefix.'ads')->where('aid = ?', $aid))) {
                    $deleteCount ++;
                }
            }
        }
        /** 提示信息 */
        $this->widget('Widget_Notice')->set($deleteCount > 0 ? _t('链接已经删除') : _t('没有链接被删除'), NULL, $deleteCount > 0 ? 'success' : 'notice');
        /** 转向原页 */
        $this->response->redirect(Typecho_Common::url('extending.php?panel=AdsPlugin%2Fmanage-ads.php', $this->options->adminUrl));
    }

	public function action()
	{
		$user = Typecho_Widget::widget('Widget_User');
		$user->pass('administrator');
		$this->db = Typecho_Db::get();
		$this->prefix = $this->db->getPrefix();
		$this->options = Typecho_Widget::widget('Widget_Options');
		$this->on($this->request->is('do=insert'))->insert();
		$this->on($this->request->is('do=update'))->update();
		$this->on($this->request->is('do=delete'))->delete();
		$this->response->redirect($this->options->adminUrl);
	}
}
