<?php
/**
 * 2015-2017 Urb-it
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Academic Free License (AFL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/afl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@prestashop.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade Urb-it to newer
 * versions in the future. If you wish to customize Urb-it for your
 * needs please refer to https://urb-it.com for more information.
 *
 * @author    Urb-it SA <parissupport@urb-it.com>
 * @copyright 2015-2017 Urb-it SA
 * @license  http://www.gnu.org/licenses/
 */

include_once(_PS_MODULE_DIR_ . 'urbitproductfeed' . DIRECTORY_SEPARATOR . 'Helper' . DIRECTORY_SEPARATOR . 'FeedHelper.php');
include_once(_PS_MODULE_DIR_ . 'urbitproductfeed' . DIRECTORY_SEPARATOR . 'Model' . DIRECTORY_SEPARATOR . 'Feed.php');

if (!defined('_PS_VERSION_')) {
    exit;
}

/**
 * Class UrbitProductfeedFeedModuleFrontController
 */
class UrbitProductfeedFeedModuleFrontController extends ModuleFrontController
{
    protected $_products;

    /**
     *
     */
    public function initContent()
    {
        parent::initContent();

        header('Content-Type: application/json');
        $this->setTemplate('module:urbitproductfeed/views/templates/front/feedtemp.tpl');

        if (Tools::getIsset(Tools::getValue(array('cron')))) {
            $this->generateByCron();
        } else {
            echo $this->getProductsJson();
        }
    }

    /**
     * Write feed to file and return feed from this file
     * @return string
     */
    public function getProductsJson()
    {
        $feedHelper = new UrbitProductfeedFeedHelper();

        if (!$feedHelper->checkCache()) {
            $context = Context::getContext();
            $categoryFilters = UrbitProductfeedFeed::getCategoryFilters();
            $tagFilters = UrbitProductfeedFeed::getTagsFilters();

            $products = UrbitProductfeedFeed::getProductsFilteredByCategoriesAndTags($context->language->id, 0, 0, 'id_product', 'DESC', $categoryFilters, $tagFilters);
            $uniqueProducts = array_unique($products, SORT_REGULAR);
            $feedHelper->generateFeed($uniqueProducts);
        }

        return $feedHelper->getDataJson();
    }

    /**
     * Write feed to file
     */
    public function generateByCron()
    {
        $feedHelper = new UrbitProductfeedFeedHelper();

        if (!$feedHelper->checkCache()) {
            $context = Context::getContext();
            $categoryFilters = UrbitProductfeedFeed::getCategoryFilters();
            $tagFilters = UrbitProductfeedFeed::getTagsFilters();

            $products = UrbitProductfeedFeed::getProductsFilteredByCategoriesAndTags($context->language->id, 0, 0, 'id_product', 'DESC', $categoryFilters, $tagFilters);
            $uniqueProducts = array_unique($products, SORT_REGULAR);
            $feedHelper->generateFeed($uniqueProducts);
        }
    }
}
