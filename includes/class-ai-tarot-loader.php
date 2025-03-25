<?php
/**
 * Hook Yükleyici
 * 
 * Bu sınıf, WordPress eylemlerini ve filtreleri kaydeder ve çalıştırır.
 */

// Güvenlik kontrolü
if (!defined('ABSPATH')) {
    exit;
}

/**
 * AI Tarot Yükleyici sınıfı
 */
class AI_Tarot_Loader {
    
    /**
     * Kaydedilen eylemler
     */
    protected $actions;
    
    /**
     * Kaydedilen filtreler
     */
    protected $filters;
    
    /**
     * Kaydedilen kısa kodlar
     */
    protected $shortcodes;
    
    /**
     * Sınıfı başlat
     */
    public function __construct() {
        $this->actions = array();
        $this->filters = array();
        $this->shortcodes = array();
    }
    
    /**
     * WordPress eylemini ekle
     */
    public function add_action($hook, $component, $callback, $priority = 10, $accepted_args = 1) {
        $this->actions = $this->add($this->actions, $hook, $component, $callback, $priority, $accepted_args);
    }
    
    /**
     * WordPress filtresini ekle
     */
    public function add_filter($hook, $component, $callback, $priority = 10, $accepted_args = 1) {
        $this->filters = $this->add($this->filters, $hook, $component, $callback, $priority, $accepted_args);
    }
    
    /**
     * WordPress kısa kodunu ekle
     */
    public function add_shortcode($tag, $component, $callback) {
        $this->shortcodes = $this->add($this->shortcodes, $tag, $component, $callback, 0, 0);
    }
    
    /**
     * Kancaları kaydet
     */
    private function add($hooks, $hook, $component, $callback, $priority, $accepted_args) {
        $hooks[] = array(
            'hook'          => $hook,
            'component'     => $component,
            'callback'      => $callback,
            'priority'      => $priority,
            'accepted_args' => $accepted_args
        );
        
        return $hooks;
    }
    
    /**
     * Kaydedilen kancaları WordPress'e kaydet
     */
    public function run() {
        // Eylemler
        foreach ($this->actions as $hook) {
            add_action(
                $hook['hook'],
                array($hook['component'], $hook['callback']),
                $hook['priority'],
                $hook['accepted_args']
            );
        }
        
        // Filtreler
        foreach ($this->filters as $hook) {
            add_filter(
                $hook['hook'],
                array($hook['component'], $hook['callback']),
                $hook['priority'],
                $hook['accepted_args']
            );
        }
        
        // Kısa kodlar
        foreach ($this->shortcodes as $hook) {
            add_shortcode(
                $hook['hook'],
                array($hook['component'], $hook['callback'])
            );
        }
    }
}