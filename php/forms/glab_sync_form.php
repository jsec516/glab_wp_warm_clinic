<?php

class glab_sync_form {

    static function generate_account_form($args) {
        extract($args);
        ?>
        <form action="" method="post">
            <table>
                <tr><td>Email Address<font color="red"> *</font>:</td><td><input type="text" name="gc_account" style="width:300px;" value="<?= ($account['g_account']) ? $account['g_account'] : ''; ?>" /></td></tr>
                <tr><td style="text-align:right;">Password<font color="red"> *</font>:</td><td><input type="password" name="gc_password" style="width:290px;" value="<?= ($account['g_password']) ? $account['g_password'] : ''; ?>" /></td></tr>
                <tr><td>&nbsp;</td><td style="text-align:right;">
                        <?php wp_nonce_field('sync_update', 'sync_update_nonce'); ?>
                        <input type="submit" value="Update"  class="button-primary" name="update_account" /></td></tr>
            </table>
        </form>
        <?php
        self::generate_calendar_heading();
    }

    static function generate_calendar_heading() {
        ?>
        <div>
            <h2 style="width:78%;display:inline-block;">Calendar List</h2>
            <div style="display:inline-block;width:20%;text-align:right;">
                <form method="POST" action="">
                    <?php wp_nonce_field('update_clist', 'update_clist_nonce'); ?>
                    <input type="submit" value="Sync Calendar List"  class="button-primary" name="update_calendar_list" />
                </form>
            </div>
        </div>
        
        <?php
    }

}
