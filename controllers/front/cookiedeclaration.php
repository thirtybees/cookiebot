<?php
/**
 * Copyright (C) 2017-2024 thirty bees
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Academic Free License (AFL 3.0)
 * that is bundled with this package in the file LICENSE.md
 * It is also available through the world-wide-web at this URL:
 * https://opensource.org/licenses/afl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@thirtybees.com so we can send you a copy immediately.
 *
 * @author    thirty bees <contact@thirtybees.com>
 * @copyright 2017-2024 thirty bees
 * @license   https://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
 */

if (!defined('_TB_VERSION_')) {
    exit;
}

/**
 * Class CookieBotCookiedeclarationModuleFrontController
 */
class CookieBotCookiedeclarationModuleFrontController extends ModuleFrontController
{
    /** @var bool $display_column_left */
    public $display_column_left = false;
    /** @var bool $display_column_right */
    public $display_column_right = false;

    /**
     * Initialize content
     *
     * @throws PrestaShopException
     */
    public function initContent()
    {
        if (!Configuration::get(CookieBot::SHOW_COOKIE_DECLARATION_PAGE)) {
            @ob_clean();
            header('Content-Type: text/plain');
            die('This page has not been enabled');
        }

        if (!$domainId = Configuration::get(CookieBot::DOMAIN_GROUP_ID)) {
            @ob_clean();
            header('Content-Type: text/plain');
            die('CookieBot ID has not been set');
        }

        $this->context->smarty->assign([
            'cookieBotDomainId' => Tools::safeOutput($domainId),
        ]);

        parent::initContent();
        $this->setTemplate('cookiedeclaration.tpl');
    }
}
