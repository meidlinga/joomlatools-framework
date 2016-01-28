<?php
/**
 * Nooku Framework - http://nooku.org/framework
 *
 * @copyright	Copyright (C) 2011 - 2014 Johan Janssens and Timble CVBA. (http://www.timble.net)
 * @license		GNU GPLv3 <http://www.gnu.org/licenses/gpl.html>
 * @link		http://github.com/nooku/nooku-files for the canonical source repository
 */
defined('KOOWA') or die;

$can_upload = isset(parameters()->config['can_upload']) ? parameters()->config['can_upload'] : true;
?>

<?= import('com:files.files.scripts.html'); ?>

<ktml:script src="media://koowa/com_files/js/files.attachments.app.js" />

<script>
    Files.sitebase = '<?= $sitebase; ?>';
    Files.token = '<?= $token; ?>';

    kQuery(function($) {
        var config = <?= json_encode(KObjectConfig::unbox(parameters()->config)); ?>,
            options = {
                cookie: {
                    path: '<?=object('request')->getSiteUrl()?>'
                },
                root_text: <?= json_encode(translate('Root folder')) ?>,
                editor: <?= json_encode(parameters()->editor); ?>,
                types: <?= json_encode(KObjectConfig::unbox(parameters()->types)); ?>,
                container: <?= json_encode($container ? $container->toArray() : null); ?>
            };
        options = Object.append(options, config);

        $('<div style="text-align: center; display: none"></div>').appendTo($('#insert-button-container'))
            .append('<button class="btn btn-primary" type="button" id="insert-button" disabled>' + Koowa.translate('Attach') + '</button>');

        $('<div style="text-align: center; display: none"></div>').appendTo($('#detach-button-container'))
            .append('<button class="btn btn-danger" type="button" id="detach-button" disabled>'+Koowa.translate('Detach')+'</button>');


        Files.app = new Files.Attachments.App(options);

        var url = Files.app.createRoute({
            view: 'attachments',
            format: 'json',
            table: Files.app.attachments.table,
            row: Files.app.attachments.row,
            routed: 0
        });

        kQuery.ajax(
            {
                url: url,
                method: 'GET',
                success: function(data) {
                    Files.app.attachments.grid.insertRows(data.entities);
                }
            }
        );

        var app = Files.app;

        app.attachments.grid.addEvent('afterDetachAttachment', function()
        {
            $('#attachments-preview').empty();

            document.id('detach-button').set('disabled', true)
                .getParent().setStyle('display', 'none');
        });

        app.addEvent('uploadFile', function(row) {
            app.grid.selected = row.path;

            kQuery('#insert-button').trigger('click');
        });

        var onClickNode = function(e) {
            var row = document.id(e.target).getParent('.files-node').retrieve('row');

            app.grid.selected = row.path;

            document.id('insert-button').set('disabled', false)
                .getParent().setStyle('display', 'block');
        };

        var onClickAttachment = function(e) {
            var row = document.id(e.target).getParent('.files-node').retrieve('row');

            app.attachments.grid.selected = row.name;

            document.id('detach-button').set('disabled', false)
                .getParent().setStyle('display', 'block');
        }

        app.grid.addEvent('clickFile', onClickNode);
        app.grid.addEvent('clickImage', onClickNode);
        app.attachments.grid.addEvent('clickAttachment', onClickAttachment);
    });

    kQuery(function($) {
        var insert_trigger = $('.koowa_dialog__menu__child--insert'),
            upload_trigger = $('.koowa_dialog__menu__child--download'),
            attachments_trigger = $('.koowa_dialog__menu__child--attachments'),
            insert_dialog = $('.koowa_dialog__file_dialog_files, .koowa_dialog__file_dialog_insert'),
            upload_dialog = $('.koowa_dialog__file_dialog_upload'),
            attachments_dialog = $('.koowa_dialog__file_dialog_attachments, .koowa_dialog__file_dialog_detach');

        // Set initially
        if (upload_dialog.length) {
            insert_dialog.hide();
            attachments_dialog.hide();
            upload_trigger.addClass('active');
        } else {
            upload_dialog.hide();
            attachments_dialog.hide();
            insert_trigger.addClass('active');
        }

        insert_trigger.click(function() {
            $(this).addClass('active')
                .siblings().removeClass('active');

            upload_dialog.hide();
            attachments_dialog.hide();
            insert_dialog.show();
        });

        upload_trigger.click(function() {
            $(this).addClass('active')
                .siblings().removeClass('active');

            insert_dialog.hide();
            attachments_dialog.hide();
            upload_dialog.show();
        });

        attachments_trigger.click(function() {
            $(this).addClass('active')
                .siblings().removeClass('active');

            insert_dialog.hide();
            upload_dialog.hide();
            attachments_dialog.show();
        });

        // Scroll to upload or insert area after click
        if ( $('body').width() <= '699' ) { // 699 is when colums go from stacked to aligned
            upload_trigger.click(function() {
                $('html, body').animate({
                    scrollTop: upload_dialog.offset().top
                }, 1000);
            });

            $('#files-grid').on('click', 'a.navigate', function() {
                $('html, body').animate({
                    scrollTop: '5000' // Scroll to highest amount so it will at least scroll to the bottom where the insert button is
                }, 1000);
            });
        }

    });
</script>

<?= import('com:files.files.templates_compact.html');?>
<?= import('com:files.attachments.app.templates');?>

<div class="koowa_dialog koowa_dialog--file_dialog">
    <div class="koowa_dialog__menu koowa_dialog__menu--fullwidth">
        <? if ($can_upload): ?>
            <a class="koowa_dialog__menu__child--download"><?= translate('Upload'); ?></a>
        <? endif; ?>
        <a class="koowa_dialog__menu__child--insert"><?= translate('Select'); ?></a>
        <a class="koowa_dialog__menu__child--attachments"><?= translate('Attachments'); ?></a>
    </div>
    <div class="koowa_dialog__layout">
        <div class="koowa_dialog__wrapper">
            <? if ($can_upload): ?>
                <div id="koowa_dialog__file_dialog_upload" class="koowa_dialog__wrapper__child koowa_dialog__file_dialog_upload koowa_dialog__file_dialog_upload--fullwidth">
                    <h2 class="koowa_dialog__title">
                        <?= translate('Upload a file to attach'); ?>
                    </h2>
                    <div class="koowa_dialog__child__content">
                        <div class="koowa_dialog__child__content__box">
                            <?= import('com:files.files.uploader.html', array('multi_selection' => false)); ?>
                        </div>
                    </div>
                </div>
            <? endif; ?>
            <div class="koowa_dialog__wrapper__child koowa_dialog__file_dialog_files">
                <h2 class="koowa_dialog__title">
                    <?= translate('Select a file to attach'); ?>
                </h2>
                <div class="koowa_dialog__child__content" id="spinner_container">
                    <div class="koowa_dialog__child__content__box">
                        <div id="files-grid" style="max-height:450px;">

                        </div>
                    </div>
                </div>
            </div>
            <div class="koowa_dialog__wrapper__child koowa_dialog__file_dialog_insert koowa_dialog__file_dialog_insert--fullwidth">
                <h2 class="koowa_dialog__title">
                    <?= translate('Selected file info'); ?>
                </h2>
                <div class="koowa_dialog__child__content">
                    <div class="koowa_dialog__child__content__box">
                        <div id="files-preview"></div>
                        <div id="insert-button-container"></div>
                    </div>
                </div>
            </div>
            <div class="koowa_dialog__wrapper__child koowa_dialog__file_dialog_attachments">
                <h2 class="koowa_dialog__title">
                    <?= translate('Attached files'); ?>
                </h2>
                <div class="koowa_dialog__child__content" id="spinner_container">
                    <div class="koowa_dialog__child__content__box">
                        <div id="attachments-grid" style="max-height:450px;">

                        </div>
                    </div>
                </div>
            </div>
            <div class="koowa_dialog__wrapper__child koowa_dialog__file_dialog_detach koowa_dialog__file_dialog_detach--fullwidth">
                <h2 class="koowa_dialog__title">
                    <?= translate('Selected attachment info'); ?>
                </h2>
                <div class="koowa_dialog__child__content">
                    <div class="koowa_dialog__child__content__box">
                        <div id="attachments-preview"></div>
                        <div id="detach-button-container"></div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>