<?php

namespace ProfilePress\Core\ShortcodeParser;

use ProfilePress\Core\ShortcodeParser\Builder\EditProfileBuilder;
use ProfilePress\Core\ShortcodeParser\Builder\GlobalShortcodes;
use ProfilePress\Core\ShortcodeParser\Builder\LoginFormBuilder;
use ProfilePress\Core\ShortcodeParser\Builder\PasswordResetBuilder;
use ProfilePress\Core\ShortcodeParser\Builder\RegistrationFormBuilder;
use ProfilePress\Core\ShortcodeParser\MyAccount\MyAccountTag;

class Init
{
    public function __construct()
    {
        GlobalShortcodes::initialize();
        EditProfileBuilder::get_instance();
        LoginFormBuilder::get_instance();
        PasswordResetBuilder::get_instance();
        RegistrationFormBuilder::initialize();

        LoginFormTag::get_instance();
        EditProfileTag::get_instance();
        FrontendProfileTag::get_instance();
        MelangeTag::get_instance();
        PasswordResetTag::get_instance();
        RegistrationFormTag::get_instance();
        MyAccountTag::get_instance();
        MemberDirectoryTag::get_instance();

        MembershipShortcodes::get_instance();
    }

    public static function get_instance()
    {
        static $instance = null;

        if (is_null($instance)) {
            $instance = new self();
        }

        return $instance;
    }
}
