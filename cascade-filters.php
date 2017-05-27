<?php
namespace Grav\Plugin;

use Grav\Common\Plugin;
use RocketTheme\Toolbox\Event\Event;

/**
 * Class CascadeFiltersPlugin
 * @package Grav\Plugin
 */
class CascadeFiltersPlugin extends Plugin
{
    public static function getSubscribedEvents()
    {
        return [
            'onPluginsInitialized' => ['onPluginsInitialized', 0]
        ];
    }

	public function onPluginsInitialized() {
        if ($this->isAdmin()) {
			$this->active = false;

            return;
        }
		$this->selected_pages = $this->config->get( 'plugins.' . $this->name . '.select_pages' );
        $this->enable([
			'onPageInitialized' => [ 'onPageInitialized', 0 ],
            'onTwigTemplatePaths' => ['onTwigTemplatePaths', 0],
		] );
	}

	public function onPageInitialized() {
		if (! in_array($this->grav['page']->rawRoute(), $this->selected_pages)) {
			return;
		}
		$this->enable([
            'onTwigSiteVariables' => ['onTwigSiteVariables', 0]
        ]);
    }

    /**
	 * Add current directory to twig lookup paths.
     */
	public function onTwigTemplatePaths() {
		$this->grav['twig']->twig_paths[] = __DIR__ . '/templates';
	}

	/**
	 * Set needed variables to display the taxonomy list.
	 */
	public function onTwigSiteVariables() {
		require_once __DIR__ . '/classes/cascade-filters.php';
		$twig    = $this->grav['twig'];
		$filters = $this->config->get( 'plugins.' . $this->name . '.taxonomy_filters' );
        $twig->twig_vars['cascadeFilters'] = new CascadeFilters($filters);
    }
}
