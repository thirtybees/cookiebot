<?php
/**
 * Copyright (C) 2018 thirty bees
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Academic Free License (AFL 3.0)
 * that is bundled with this package in the file LICENSE.md
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/afl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@thirtybees.com so we can send you a copy immediately.
 *
 * @author    thirty bees <contact@thirtybees.com>
 * @copyright 2018 thirty bees
 * @license   http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
 */

if (!defined('_TB_VERSION_')) {
    exit;
}

/**
 * Class CookieBot
 */
class CookieBot extends Module
{
    const DOMAIN_GROUP_ID = 'COOKIEBOT_GROUP_ID';
    const SHOW_COOKIE_DECLARATION_PAGE = 'COOKIEBOT_COOKIE_DECL_PAGE';

    /**
     * BeesBlog constructor.
     *
     * @since 1.0.0
     * @throws PrestaShopException
     */
    public function __construct()
    {
        $this->name = 'cookiebot';
        $this->tab = 'front_office_features';
        $this->version = '1.0.0';
        $this->author = 'thirty bees';

        $this->controllers = ['cookiedeclaration'];
        $this->bootstrap = true;

        parent::__construct();
        $this->displayName = $this->l('Cookiebot');
        $this->description = $this->l('Cookiebot helps make your use of cookies and online tracking GDPR and EPR compliant');
    }

    /**
     * Install this module
     *
     * @return bool Whether the module has been successfully installed
     *
     * @throws PrestaShopDatabaseException
     * @throws PrestaShopException
     * @since 1.0.0
     */
    public function install()
    {
        if (!parent::install()) {
            return false;
        }

        $this->registerHook('displayHeader');

        return true;
    }

    /**
     * Uninstall this module
     *
     * @return bool Whether the module has been successfully uninstalled
     *
     * @throws PrestaShopDatabaseException
     * @throws PrestaShopException
     * @since 1.0.0
     */
    public function uninstall()
    {
        if (!parent::uninstall()) {
            return false;
        }

        return true;
    }

    /**
     * @return string
     * @throws Exception
     * @throws PrestaShopDatabaseException
     * @throws PrestaShopException
     * @throws SmartyException
     */
    public function getContent()
    {
        $this->postProcess();

        return $this->generateCredentialsForm();
    }

    /**
     * @throws PrestaShopException
     */
    protected function postProcess()
    {
        if (Tools::getValue('submitCredentials')) {
            Configuration::updateValue(static::DOMAIN_GROUP_ID, Tools::getValue(static::DOMAIN_GROUP_ID));
            Configuration::updateValue(static::SHOW_COOKIE_DECLARATION_PAGE, Tools::getValue(static::SHOW_COOKIE_DECLARATION_PAGE));
        }
    }

    /**
     * @return string
     *
     * @throws Exception
     * @throws PrestaShopDatabaseException
     * @throws PrestaShopException
     * @throws SmartyException
     * @since 1.0.0
     */
    protected function generateCredentialsForm()
    {
        $declarationPage = $this->context->link->getModuleLink($this->name, 'cookiedeclaration', [], true);
        $fields = [
            'form' => [
                'legend' => [
                    'title' => $this->l('Cookiebot settings'),
                    'icon'  => 'icon-cogs',
                ],
                'input'  => [
                    [
                        'type'        => 'text',
                        'label'       => $this->l('Domain Group ID'),
                        'name'        => self::DOMAIN_GROUP_ID,
                        'placeholder' => '7dc55a80-69d4-11e8-adc0-fa7ae01bbebc',
                    ],
                    [
                        'type'    => 'switch',
                        'label'   => $this->l('Enable cookie declaration page'),
                        'desc'    => static::ppTags($this->l('Click [1]here[/1] to navigate to the declaration page'), ["<a href='{$declarationPage}' target='_blank' rel='noopener noreferrer'>"]),
                        'name'    => static::SHOW_COOKIE_DECLARATION_PAGE,
                        'is_bool' => true,
                        'values'  => [
                            [
                                'id'    => 'active_on',
                                'value' => true,
                                'label' => Translate::getAdminTranslation('Enabled', 'AdminCarriers'),
                            ],
                            [
                                'id'    => 'active_off',
                                'value' => false,
                                'label' => Translate::getAdminTranslation('Disabled', 'AdminCarriers'),
                            ],
                        ],
                    ],
                ],
                'submit' => [
                    'title' => $this->l('Save'),
                    'class' => 'btn btn-default pull-right',
                ],
            ],
        ];

        $helper = new HelperForm();
        $helper->currentIndex = $this->context->link->getAdminLink('AdminModules', true).'&configure='.$this->name.'&tab_module='.$this->tab.'&module_name='.$this->name;
        $helper->token = '';
        $helper->submit_action = 'submitCredentials';
        $helper->tpl_vars = [
            'fields_value' => $this->getConfigFieldsValues(),
        ];

        return $helper->generateForm([$fields]);
    }

    /**
     * @return array
     * @throws PrestaShopException
     */
    protected function getConfigFieldsValues()
    {
        return [
            static::DOMAIN_GROUP_ID              => Configuration::get(static::DOMAIN_GROUP_ID),
            static::SHOW_COOKIE_DECLARATION_PAGE => Configuration::get(static::SHOW_COOKIE_DECLARATION_PAGE),
        ];
    }

    /**
     * Register the module routes
     *
     * @return string Array with routes
     * @throws PrestaShopException
     * @throws SmartyException
     */
    public function hookDisplayHeader()
    {
        $this->context->smarty->assign([
            'cookieBotDomainId' => Configuration::get(static::DOMAIN_GROUP_ID),
        ]);

        return $this->display(__FILE__, 'cookiebot.tpl');
    }

    /**
     * Post process tags in (translated) strings
     *
     * @param string $string
     * @param array  $tags
     *
     * @return string
     */
    public static function ppTags($string, $tags = array())
    {
        // If tags were explicitely provided, we want to use them *after* the translation string is escaped.
        if (!empty($tags)) {
            foreach ($tags as $index => $tag) {
                // Make positions start at 1 so that it behaves similar to the %1$d etc. sprintf positional params
                $position = $index + 1;
                // extract tag name
                $match = array();
                if (preg_match('/^\s*<\s*(\w+)/', $tag, $match)) {
                    $opener = $tag;
                    $closer = '</'.$match[1].'>';

                    $string = str_replace('['.$position.']', $opener, $string);
                    $string = str_replace('[/'.$position.']', $closer, $string);
                    $string = str_replace('['.$position.'/]', $opener.$closer, $string);
                }
            }
        }

        return $string;
    }
}
