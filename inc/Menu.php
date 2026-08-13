<?php

namespace Nomreh;

class Menu{

    public function __construct(){
        add_action('admin_menu', [$this, 'add_submenu']);
    }

    public function add_submenu() {
        add_submenu_page(
            'options-general.php',
            'نُمره',
            'نُمره',
            'manage_options',
            'nomreh',
            [$this, 'page_content']
        );
    }

    public function page_content() {
        $active_tab = $_GET['tab'] ?? 'settings';
        ?>
        <div class="wrap">
            <h2>نُمره</h2>

            <h2 class="nav-tab-wrapper">
                <a href="?page=nomreh&tab=settings" class="nav-tab <?php echo $active_tab === 'settings' ? 'nav-tab-active' : ''; ?>">تنظیمات</a>
                <a href="?page=nomreh&tab=styles" class="nav-tab <?php echo $active_tab === 'styles' ? 'nav-tab-active' : ''; ?>">استایل</a>
                <a href="?page=nomreh&tab=logs" class="nav-tab <?php echo $active_tab === 'logs' ? 'nav-tab-active' : ''; ?>">لاگ ورود</a>
                <a href="?page=nomreh&tab=tools" class="nav-tab <?php echo $active_tab === 'tools' ? 'nav-tab-active' : ''; ?>">ابزارها</a>
            </h2>

            <?php include_once dirname(__FILE__) . '/menu/'.$active_tab.'.php';?>


        </div>
        <?php
    }
}
