<?php

namespace Drupal\dartharth_telegram_alert\Form;

use Drupal\Core\Form\ConfigFormBase;
use Drupal\Core\Form\FormStateInterface;

/**
 * Class SettingsForm.
 */
class SettingsForm extends ConfigFormBase {

    /**
     * {@inheritdoc}
     */
    protected function getEditableConfigNames() {
        return [
            'dartharth_telegram_alert.settings',
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function getFormId() {
        return 'dartharth_telegram_alert_settings_form';
    }

    /**
     * {@inheritdoc}
     */
    public function buildForm(array $form, FormStateInterface $form_state) {
        $config = $this->config('dartharth_telegram_alert.settings');

        $form['telegram_bot_token'] = [
            '#type' => 'textfield',
            '#title' => $this->t('Telegram Bot Token'),
            '#description' => $this->t('Enter the token for your Telegram bot.'),
            '#default_value' => $config->get('telegram_bot_token'),
            '#required' => TRUE,
        ];

        $form['telegram_chat_id'] = [
            '#type' => 'textfield',
            '#title' => $this->t('Telegram Chat ID'),
            '#description' => $this->t('Enter the chat ID for where the messages should be sent.'),
            '#default_value' => $config->get('telegram_chat_id'),
            '#required' => TRUE,
        ];


        $webform_ids = \Drupal::entityQuery('webform')->execute();
        $webforms = \Drupal\webform\Entity\Webform::loadMultiple($webform_ids);

        $form['webforms'] = [
            '#type' => 'checkboxes',
            '#title' => $this->t('Select Webforms'),
            '#description' => $this->t('Select the webforms that you want to send alerts from.'),
            '#options' => [],
        ];

        foreach ($webforms as $webform) {
            // Check if the webform is not a template and is open.
            if (!$webform->isTemplate() && $webform->isOpen()) {
                $form['webforms']['#options'][$webform->id()] = $webform->label();
                // Set the default value if it was previously selected.
                $form['webforms'][$webform->id()] = [
                    '#default_value' => $config->get('webforms.' . $webform->id()),
                ];
            }
        }

        $form['notify_orders'] = [
            '#type' => 'checkbox',
            '#title' => $this->t('Notify on new orders'),
            '#description' => $this->t('Check this box to enable notifications for new orders modules Basic_cart.'),
            '#default_value' => $config->get('notify_orders'),
        ];

        return parent::buildForm($form, $form_state);
    }

    /**
     * {@inheritdoc}
     */
    public function submitForm(array &$form, FormStateInterface $form_state) {
        $values = $form_state->getValues();
        $config = $this->config('dartharth_telegram_alert.settings');

        $config->set('telegram_bot_token', $values['telegram_bot_token']);
        $config->set('telegram_chat_id', $values['telegram_chat_id']);

        foreach ($values['webforms'] as $webform_id => $enabled) {
            $config->set('webforms.' . $webform_id, $enabled);
        }

        $config->set('notify_orders', $values['notify_orders']);

        $config->save();

        parent::submitForm($form, $form_state);
    }

}
