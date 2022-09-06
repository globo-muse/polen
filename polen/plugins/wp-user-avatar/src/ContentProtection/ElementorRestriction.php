<?php

namespace ProfilePress\Core\ContentProtection;

use Elementor\Controls_Manager;
use Elementor\Element_Base;
use Elementor\Widget_Base as Widget_Base;
use ProfilePress\Core\Membership\Models\Customer\CustomerFactory;
use ProfilePress\Core\Membership\Repositories\PlanRepository;

class ElementorRestriction
{
    public function __construct()
    {
        add_action('elementor/element/common/_section_style/after_section_end', [$this, 'register_section']);
        add_action('elementor/element/section/section_advanced/after_section_end', [$this, 'register_section']);
        add_action('elementor/element/column/section_advanced/after_section_end', [$this, 'register_section']);
        add_action('elementor/element/container/section_layout/after_section_end', [$this, 'register_section']);

        add_action('elementor/element/common/ppress_section/before_section_end', array($this, 'register_controls'), 10, 2);
        add_action('elementor/element/section/ppress_section/before_section_end', array($this, 'register_controls'), 10, 2);
        add_action('elementor/element/column/ppress_section/before_section_end', array($this, 'register_controls'), 10, 2);
        add_action('elementor/element/container/ppress_section/before_section_end', array($this, 'register_controls'), 10, 2);


        add_filter('elementor/frontend/widget/should_render', [$this, 'should_render'], PHP_INT_MAX - 10, 2);
        add_filter('elementor/frontend/section/should_render', [$this, 'should_render'], PHP_INT_MAX - 10, 2);
        add_filter('elementor/frontend/column/should_render', [$this, 'should_render'], PHP_INT_MAX - 10, 2);
        add_filter('elementor/frontend/container/should_render', [$this, 'should_render'], PHP_INT_MAX - 10, 2);

        // determine whether to replace widget's content with "Content Restricted" alert message or not
        add_filter('elementor/widget/render_content', [$this, 'maybe_render_restricted_message'], 10, 2);
    }

    public function register_section($element)
    {
        $element->start_controls_section('ppress_section', [
            'label' => __('ProfilePress Content Restriction', 'wp-user-avatar'),
            'tab'   => Controls_Manager::TAB_ADVANCED,
        ]);

        $element->end_controls_section();
    }

    public function register_controls($element, $args)
    {
        if (is_a($element, '\Elementor\Core\DocumentTypes\Post')) return;

        $element->add_control(
            'ppress_visibility',
            [
                'label'       => __('Target Audience', 'wp-user-avatar'),
                'type'        => Controls_Manager::SELECT,
                'default'     => 'everyone',
                'options'     => [
                    'everyone'  => __('Everyone', 'wp-user-avatar'),
                    'loggedin'  => __('Logged-in Users', 'wp-user-avatar'),
                    'loggedout' => __('Logged-out Users', 'wp-user-avatar'),
                ],
                'multiple'    => false,
                'label_block' => true,
            ]
        );

        $element->add_control(
            'ppress_membership_plans',
            [
                'label'       => __('Restrict to Membership Plans', 'wp-user-avatar'),
                'type'        => Controls_Manager::SELECT2,
                'options'     => (function () {

                    $plans = PlanRepository::init()->retrieveAll();

                    $result = [];
                    foreach ($plans as $plan) {
                        $result[$plan->id] = $plan->name;
                    }

                    return $result;
                })(),
                'multiple'    => true,
                'label_block' => true,
                'condition'   => [
                    'ppress_visibility' => ['loggedin'],
                ],
            ]
        );

        $element->add_control(
            'ppress_user_roles',
            [
                'label'       => __('Restrict to User Roles', 'wp-user-avatar'),
                'type'        => Controls_Manager::SELECT2,
                'options'     => (function () {

                    $all_roles = ppress_get_editable_roles(false);

                    $result = [];
                    foreach ($all_roles as $key => $value) {
                        $result[$key] = $value['name'];
                    }

                    return $result;
                })(),
                'multiple'    => true,
                'label_block' => true,
                'condition'   => [
                    'ppress_visibility' => ['loggedin'],
                ],
            ]
        );

        // additional controls for Widget based elements only
        if ($element instanceof Widget_Base) {

            $element->add_control(
                'ppress_alternate_content',
                [
                    'label'       => __('Alternate Content', 'wp-user-avatar'),
                    'description' => __('The message to show when the main content is restricted.', 'wp-user-avatar'),
                    'type'        => Controls_Manager::SELECT,
                    'default'     => 'nothing',
                    'options'     => [
                        'nothing' => __('Show Nothing', 'wp-user-avatar'),
                        'global'  => __('Global Restrict Access Message', 'wp-user-avatar'),
                        'custom'  => __('Custom Message', 'wp-user-avatar'),
                    ],
                    'multiple'    => false,
                    'label_block' => true,
                    'condition'   => [
                        'ppress_visibility' => ['loggedin', 'loggedout']
                    ],
                ]
            );

            $element->add_control(
                'ppress_custom_message',
                [
                    'type'        => Controls_Manager::WYSIWYG,
                    'label'       => __('Custom Message', 'wp-user-avatar'),
                    'description' => __('This message will be shown as an alternate of your content', 'wp-user-avatar'),
                    'default'     => __('This content is restricted!', 'wp-user-avatar'),
                    'condition'   => [
                        'ppress_alternate_content' => 'custom',
                        'ppress_visibility'        => ['loggedin', 'loggedout']
                    ],
                ]
            );
        }
    }

    /**
     * @param Element_Base $element
     *
     * @return bool
     */
    private function can_access($element)
    {
        if (is_admin() || \Elementor\Plugin::$instance->editor->is_edit_mode()) {
            return true;
        }

        $visibility       = $element->get_settings('ppress_visibility');
        $roles            = $element->get_settings('ppress_user_roles');
        $membership_plans = $element->get_settings('ppress_membership_plans');

        if (empty($visibility) || 'everyone' == $visibility) return true;

        if ($visibility == 'loggedout' && ! is_user_logged_in()) return true;

        // if we got here, rule is for logged in users. return false of not logged in.
        if ( ! is_user_logged_in()) return false;

        if (empty($roles) && empty($membership_plans)) return true;

        if ( ! empty($membership_plans)) {

            $customer = CustomerFactory::fromUserId(get_current_user_id());

            $membership_plans = array_map('absint', $membership_plans);

            foreach ($membership_plans as $plan_id) {
                if ($customer->has_active_subscription($plan_id)) return true;
            }
        }

        if ( ! empty($roles)) {

            $user_roles = wp_get_current_user()->roles;

            if ( ! empty(array_intersect($roles, $user_roles))) return true;
        }

        return false;
    }

    /**
     * @param Element_Base $widget
     *
     * @return bool
     */
    private function is_alternate_message_active($widget)
    {
        $setting = $widget->get_settings('ppress_alternate_content');

        return in_array($setting, ['custom', 'global']);
    }

    /**
     * @param bool $should_render
     * @param Element_Base $widget
     *
     * @return bool
     */
    public function should_render($should_render, $widget)
    {
        if ( ! $this->is_alternate_message_active($widget) && ! $this->can_access($widget)) {
            $should_render = false;
        }

        return $should_render;
    }

    /**
     * Renders the "Content restricted" message instead of the widget's content.
     *
     * Applies if widget should be hidden from members and "Content restricted" message option is enabled.
     *
     * @param string $widget_content Elementor Widget content
     * @param Widget_Base $widget Elementor Widget object
     *
     * @return string
     *
     */
    public function maybe_render_restricted_message($widget_content, $widget)
    {
        if (
            $this->is_alternate_message_active($widget) &&
            ! $this->can_access($widget)
        ) {

            $message = $widget->get_settings('ppress_custom_message');
            if (empty($message)) {
                $message = esc_html__('This content is restricted!', 'wp-user-avatar');
            }

            if ('global' == $widget->get_settings('ppress_alternate_content')) {

                $message = ppress_settings_by_key(
                    'global_restricted_access_message',
                    esc_html__('You are unauthorized to view this page.', 'wp-user-avatar'),
                    true
                );
            }

            return do_shortcode(wpautop($message));
        }

        return $widget_content;
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
