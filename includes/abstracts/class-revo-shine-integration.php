<?php

if (!defined('ABSPATH')) {
    exit;
}

if (!class_exists('Revo_Shine_Integration')) {
    /**
     * Abstract class for integration with other plugins
     * Integration class must be extends this class
     * 
     * This class can't be instantiated
     * 
     * @since       7.3.4
     */
    abstract class Revo_Shine_Integration
    {
        private $is_plugin_active = false;

        protected function set_plugin_status(bool $status = false): void
        {
            $this->is_plugin_active = $status;
        }

        public function get_plugin_status(): bool
        {
            return $this->is_plugin_active;
        }

        protected function collect_plugin(string $plugin): bool
        {
            $this->set_plugin_status(is_plugin_active($plugin));

            return $this->is_plugin_active;
        }
    }
}
