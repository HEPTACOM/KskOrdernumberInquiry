<?php

namespace KskOrdernumberInquiry;

use Enlight_Components_Mail;
use Enlight_Components_Snippet_Namespace;
use Enlight_Event_EventArgs;
use KskOrdernumberInquiry\Services\AccessEnforcer;
use Shopware\Components\Plugin;
use Shopware_Components_Snippet_Manager;
use Shopware_Controllers_Frontend_Forms;

/**
 * Class KskOrdernumberInquiry
 * @package KskOrdernumberInquiry
 */
class KskOrdernumberInquiry extends Plugin
{
    const FORM_NAME_ORDERNUMBER = 'ksk_ordernumber_inquiry_form_ordernumber';

    /**
     * @return array
     */
    public static function getSubscribedEvents()
    {
        return [
            'Enlight_Controller_Action_PreDispatch_Frontend_Forms' => 'addOrdernumberField',
            'Shopware_Controllers_Frontend_Forms_commitForm_Mail' => 'alterMailTemplate',
        ];
    }

    /**
     * @param Enlight_Event_EventArgs $args
     */
    public function addOrdernumberField(Enlight_Event_EventArgs $args)
    {
        /** @var Shopware_Controllers_Frontend_Forms $controller */
        $controller = $args->get('subject');

        /** @var AccessEnforcer $ae */
        $ae = $this->container->get('ksk_ordernumber_inquiry.services.access_enforcer');

        $namespace = $this->getSnippetNamespace();


        if ($controller->Request()->getActionName() !== 'index'
            || ($ordernumber = $controller->Request()->getParam('sOrdernumber')) === null
            || $controller->Request()->getParam('sInquiry') !== 'detail') {
            return;
        }

        /** @var array $_elements */
        $_elements = $ae->forceRead($controller, '_elements');

        $_elements[] = [
            'name' => static::FORM_NAME_ORDERNUMBER,
            'note' => '',
            'typ' => 'text',
            'required' => '0',
            'label' => $namespace->get('OrdernumberLabel'),
            'class' => 'normal',
            'value' => htmlentities($ordernumber),
            'error_msg' => '',
        ];

        $ae->forceWrite($controller, '_elements', $_elements);
    }

    /**
     * @param Enlight_Event_EventArgs $args
     */
    public function alterMailTemplate(Enlight_Event_EventArgs $args)
    {
        /** @var Shopware_Controllers_Frontend_Forms $controller */
        $controller = $args->get('subject');
        /** @var Enlight_Components_Mail $mail */
        $mail = $args->getReturn();

        $namespace = $this->getSnippetNamespace();

        if (empty($ordernumber = $controller->Request()->getParam(static::FORM_NAME_ORDERNUMBER))) {
            return;
        }

        $body = $mail->getBodyText()->getRawContent();
        $body .= PHP_EOL . $namespace->get('OrdernumberLabel') . ': ' . htmlentities($ordernumber);

        $mail->setBodyText($body);
        $args->setReturn($mail);
    }

    /**
     * @return Enlight_Components_Snippet_Namespace
     */
    private function getSnippetNamespace()
    {
        /** @var Shopware_Components_Snippet_Manager $snippetManager */
        $snippetManager = $this->container->get('snippets');
        return $snippetManager->getNamespace('frontend/forms/elements');
    }
}
