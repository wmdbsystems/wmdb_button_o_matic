<?php
namespace WMDB\ButtonOMatic\Hooks;

/*
 * This file is part of the TYPO3 CMS project.
 *
 * It is free software; you can redistribute it and/or modify it under
 * the terms of the GNU General Public License, either version 2
 * of the License, or any later version.
 *
 * For the full copyright and license information, please read the
 * LICENSE.txt file that was distributed with this source code.
 *
 * The TYPO3 project - inspiring people to share!
 */

use TYPO3\CMS\Backend\Template\Components\Buttons\InputButton;
use TYPO3\CMS\Backend\Template\Components\Buttons\SplitButton;
use TYPO3\CMS\Core\Utility\GeneralUtility;

class ButtonBarHook
{
    /**
     * Button configuration read from extConf
     *
     * @var array
     */
    protected $buttonConfig = null;

    /**
     * Makes the buttons ugly again
     *
     * @param array $params
     */
    public function uglifyButtons(array $params)
    {
        foreach ($params['buttons'] as $panelIndex => $buttonPanel) {
            foreach ($buttonPanel as $group => $buttons) {
                foreach ($buttons as $index => $button) {
                    if ($button instanceof SplitButton) {
                        if ($this->buttonConfig === null) {
                            $this->buttonConfig = $this->getExtensionConfiguration();
                        }
                        $buttonItems = $button->getButton();
                        if ($this->buttonConfig['unsplit'] === '1') {
                            // Extract Primary Button
                            $params['buttons'][$panelIndex][$group][] = $buttonItems['primary'];
                            foreach ($buttonItems['options'] as $option) {
                                $params['buttons'][$panelIndex][$group][] = $option;
                            }
                            // Remove original button
                            unset($params['buttons'][$panelIndex][$group][$index]);
                        } elseif ($this->buttonConfig['primaryRegex'] !== '') {
                            $newSplitButton = GeneralUtility::makeInstance(SplitButton::class);
                            $pattern = '/' . $this->buttonConfig['primaryRegex'] . '/';
                            $originalPrimaryAction = $buttonItems['primary'];
                            /** @var InputButton $option */
                            $keepSearchingForPrimaryAction = true;
                            foreach ($buttonItems['options'] as $option) {
                                if (preg_match($pattern, $option->getName()) === 1 && $keepSearchingForPrimaryAction) {
                                    $newSplitButton->addItem($option, true);
                                    $keepSearchingForPrimaryAction = false;
                                } else {
                                    $newSplitButton->addItem($option);
                                }
                            }
                            $newSplitButton->addItem($originalPrimaryAction);
                            $params['buttons'][$panelIndex][$group][$index] = $newSplitButton;
                        }
                    }
                }
            }
        }
        return $params['buttons'];
    }

    /**
     * @return mixed
     */
    protected function getExtensionConfiguration()
    {
        $conf = [
            'unsplit' => 0,
            'primaryRegex' => ''
        ];
        if (isset($GLOBALS['TYPO3_CONF_VARS']['EXT']['extConf']['wmdb_button_o_matic'])) {
            $tempoaryConf = unserialize($GLOBALS['TYPO3_CONF_VARS']['EXT']['extConf']['wmdb_button_o_matic']);
            if (is_array($tempoaryConf)) {
                $conf = $tempoaryConf;
            }
        }
        return $conf;
    }
}
