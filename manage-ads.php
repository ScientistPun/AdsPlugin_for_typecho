<?php
include 'common.php';
include 'header.php';
include 'menu.php';
?>


<div class="main">
    <div class="body container">
        <?php include 'page-title.php'; ?>
        <div class="row typecho-page-main manage-metas">
            <div class="col-mb-12">
                <ul class="typecho-option-tabs clearfix">
                    <li class="current"><a href="<?php $options->adminUrl('extending.php?panel=AdsPlugin%2Fmanage-ads.php'); ?>"><?php _e('广告位'); ?></a></li>
                    <li><a href="https://doc.koalilab.top/typecho_adsplugin/" title="查看广告位使用帮助" target="_blank"><?php _e('帮助'); ?></a></li>
                </ul>
            </div>

            <div class="col-mb-12 col-tb-8" role="main">
                <?php
                $prefix = $db->getPrefix();
                $ads = $db->fetchAll($db->select()->from($prefix . 'ads'));
                ?>
                <form method="post" name="manage_categories" class="operate-form">
                    <div class="typecho-list-operate clearfix">
                        <div class="operate">
                            <label><i class="sr-only"><?php _e('全选'); ?></i><input type="checkbox" class="typecho-table-select-all" /></label>
                            <div class="btn-group btn-drop">
                                <button class="btn dropdown-toggle btn-s" type="button"><i class="sr-only"><?php _e('操作'); ?></i><?php _e('选中项'); ?> <i class="i-caret-down"></i></button>
                                <ul class="dropdown-menu">
                                    <li><a lang="<?php _e('你确认要删除这些广告位吗?'); ?>" href="<?php $options->index('/action/ads-edit?do=delete'); ?>"><?php _e('删除'); ?></a></li>
                                </ul>
                            </div>
                        </div>
                    </div>

                    <div class="typecho-table-wrap">
                        <table class="typecho-list-table">
                            <thead>
                                <tr>
                                    <th> </th>
                                    <th><?php _e('关键字'); ?></th>
                                    <th><?php _e('名称'); ?></th>
                                    <th><?php _e('展示方式'); ?></th>
                                    <th><?php _e('宽度'); ?></th>
                                    <th><?php _e('高度'); ?></th>
                                    <th><?php _e('状态'); ?></th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (!empty($ads)) : $alt = 0; ?>
                                    <?php foreach ($ads as $ad) : ?>
                                        <tr id="aid-<?php echo $ad['aid']; ?>">
                                            <td><input type="checkbox" value="<?php echo $ad['aid']; ?>" name="aid[]" /></td>
                                            <td><a href="<?php echo $request->makeUriByRequest('aid=' . $ad['aid']); ?>" title="点击编辑"><?php echo $ad['keyword']; ?></a></td>
                                            <td><?php echo $ad['name']; ?></td>
                                            <td><?php if ($ad['type'] == 0) echo '图片展示';
                                                elseif ($ad['type'] == 1) echo '代码块';
                                                else echo '轮播图展示'; ?></td>
                                            <td><?php echo $ad['width'] ? "{$ad['width']}px":"自适应"; ?></td>
                                            <td><?php echo $ad['height'] ? "{$ad['height']}px":"自适应"; ?></td>
                                            <td><?php echo $ad['status'] == 1 ? '启用' : '禁用'; ?></td>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php else : ?>
                                    <tr>
                                        <td colspan="7">
                                            <h6 class="typecho-list-table-title"><?php _e('没有任何内容'); ?></h6>
                                        </td>
                                    </tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </form>
            </div>
            <div class="col-mb-12 col-tb-4" role="form">
                <?php AdsPlugin_Plugin::form()->render(); ?>
            </div>
        </div>
    </div>
</div>

<?php
include 'copyright.php';
include 'common-js.php';
?>

<script type="text/javascript">
    (function() {
        $(document).ready(function() {
            var table = $('.typecho-list-table');

            table.tableSelectable({
                checkEl: 'input[type=checkbox]',
                rowEl: 'tr',
                selectAllEl: '.typecho-table-select-all',
                actionEl: '.dropdown-menu a'
            });

            $('.btn-drop').dropdownMenu({
                btnEl: '.dropdown-toggle',
                menuEl: '.dropdown-menu'
            });

            $('.dropdown-menu button.merge').click(function() {
                var btn = $(this);
                btn.parents('form').attr('action', btn.attr('rel')).submit();
            });

            <?php if (isset($request->a)) : ?>
                $('.typecho-mini-panel').effect('highlight', '#AACB36');
            <?php endif; ?>
        });
    })();
</script>
<?php include 'footer.php'; ?>