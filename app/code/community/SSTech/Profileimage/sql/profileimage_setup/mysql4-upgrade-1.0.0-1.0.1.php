<?php

$installer = $this;

$installer->startSetup();

$widgetType = 'profileimage/widget_upload';
$template = 'profileimage/widget/account.phtml';
$widgetLabel = 'title';
$widgetLabelValue = 'Profileimage';

$handle = '<reference name="content">'
            .'<block type="'.$widgetType.'" name="profileimage.fieldset" template="'.$template.'">'
            .'<action method="setData">'
            .'<name>'.$widgetLabel.'</name>'
            .'<value>'.$widgetLabelValue.'</value>'
            .'</action>'
            .'</block>'
            .'</reference>';

$layoutUpdate = array(
    array(
        'handle'            => 'customer_account_create',
        'xml'               => $handle,
        'sort_order'        => 0
    )
);

$installer->getConnection()->insertMultiple($installer->getTable('core/layout_update'), $layoutUpdate);
$lastLayoutUpdateId = $installer->getConnection()->lastInsertId($installer->getTable('core/layout_update'));


$layoutLink = array(
    array(
        'store_id'          => 0,
        'area'              => 'frontend',
        'package'           => 'default',
        'theme'             => 'default',
        'layout_update_id'  => $lastLayoutUpdateId
    )
);

$installer->getConnection()->insertMultiple($installer->getTable('core/layout_link'), $layoutLink);

$widgetParams = array(
    'title' => 'Choose Profileimage'
);

$data = array(
    array(
        'instance_type'     => $widgetType,
        'package_theme'     => 'default/default',
        'title'             => 'Upload Profileimage Form Fieldset',
        'store_ids'         => 0,
        'widget_parameters' => serialize($widgetParams),
    )
);

$installer->getConnection()->insertMultiple($installer->getTable('widget/widget_instance'), $data);

$widgetPage = array(
    array(
        'instance_id'       => $installer->getConnection()->lastInsertId($installer->getTable('widget/widget_instance')),
        'page_group'        => 'pages',
        'layout_handle'     => 'customer_account_create',
        'block_reference'   => 'content',
        'page_for'          => 'all',
        'entities'          => '',
        'page_template'     => $template
    )
);

$installer->getConnection()->insertMultiple($installer->getTable('widget/widget_instance_page'), $widgetPage);

$widgetPageLayout = array(
    array(
        'page_id'           => $installer->getConnection()->lastInsertId($installer->getTable('widget/widget_instance_page')),
        'layout_update_id'  =>  $lastLayoutUpdateId
    )
);

$installer->getConnection()->insertMultiple($installer->getTable('widget/widget_instance_page_layout'), $widgetPageLayout);


$installer->endSetup(); 
?>
