<?php

/**
 * Contao Open Source CMS
 *
 * Copyright (c) 2016 Heimrich & Hannot GmbH
 *
 * @package haste_plus
 * @author  Dennis Patzer
 * @license http://www.gnu.org/licences/lgpl-3.0.html LGPL
 */

namespace HeimrichHannot\Haste\Util;


class Salutations
{
    /**
     * Creates complete names by inserting an array of the person's data
     *
     * Supported field names: firstname, lastname, academicTitle, additionalTitle, gender
     *
     * If some of the fields shouldn't go into the processed name, just leave them out of $arrData
     *
     * @param array $arrData
     */
    public static function createNameByFields($strLanguage, array $arrData)
    {
        if ($strLanguage)
        {
            \Controller::loadLanguageFile('default', $strLanguage, true);
        }

        $strName = '';

        if ($arrData['firstname'])
        {
            $strName = $arrData['firstname'] . ($arrData['lastname'] ? ' ' . $arrData['lastname'] : '');
        }
        elseif ($arrData['lastname'])
        {
            $strName = $arrData['lastname'];
        }

        if ($strName && $arrData['academicTitle'])
        {
            $strName = $arrData['academicTitle'] . ' ' . $strName;
        }

        if ($strName && $arrData['additionalTitle'])
        {
            $strName = $arrData['additionalTitle'] . ' ' . $strName;
        }

        if ($arrData['lastname'] && $arrData['gender'] && ($strLanguage != 'en' || !$arrData['academicTitle']))
        {
            $strGender = $GLOBALS['TL_LANG']['MSC']['haste_plus']['gender' . ($arrData['gender'] == 'female' ? 'Female' : 'Male')];

            $strName = $strGender . ' ' . $strName;
        }

        if ($strLanguage)
        {
            \Controller::loadLanguageFile('default', $GLOBALS['TL_LANGUAGE'], true);
        }

        return $strName;
    }

    /**
     * @param $strLanguage
     * @param $varEntity object|array
     *
     * @return string
     */
    public static function createSalutation($strLanguage, $varEntity, $blnInformal = false, $blnInformalFirstname = false)
    {
        if (is_array($varEntity))
        {
            $varEntity = Arrays::arrayToObject($varEntity);
        }

        $blnHasFirstname = $varEntity->firstname;
        $blnHasLastname  = $varEntity->lastname;
        $blnHasTitle     = $varEntity->title && $varEntity->title != '-' && $varEntity->title != 'Titel' && $varEntity->title != 'Title';

        if ($strLanguage)
        {
            \Controller::loadLanguageFile('default', $strLanguage, true);
        }

        switch ($strLanguage)
        {
            case 'en':
                if ($blnInformal)
                {
                    if ($blnHasFirstname && $blnInformalFirstname)
                    {
                        $strSalutation = $GLOBALS['TL_LANG']['MSC']['haste_plus']['salutation'] . ' ' . $varEntity->firstname;
                    }
                    elseif ($blnHasLastname && !$blnInformalFirstname)
                    {
                        $strSalutation = $GLOBALS['TL_LANG']['MSC']['haste_plus']['salutation'] . ' ' . $varEntity->lastname;
                    }
                    else
                    {
                        $strSalutation = $GLOBALS['TL_LANG']['MSC']['haste_plus']['salutation'];
                    }
                }
                elseif ($blnHasLastname)
                {
                    if ($blnHasTitle)
                    {
                        $strSalutation = $GLOBALS['TL_LANG']['MSC']['haste_plus']['salutation'] . ' ' . $varEntity->title;
                    }
                    else
                    {
                        $strSalutation =
                            $GLOBALS['TL_LANG']['MSC']['haste_plus']['salutation' . ($varEntity->gender == 'female' ? 'Female' : 'Male')];
                    }

                    $strSalutation = $strSalutation . ' ' . $varEntity->lastname;
                }
                else
                {
                    $strSalutation = $GLOBALS['TL_LANG']['MSC']['haste_plus']['salutationGeneric'];
                }
                break;
            default:
                // de
                if ($blnInformal)
                {
                    if ($blnHasFirstname && $blnInformalFirstname)
                    {
                        $strSalutation = $GLOBALS['TL_LANG']['MSC']['haste_plus']['salutationGenericInformal'] . ' ' . $varEntity->firstname;
                    }
                    elseif ($blnHasLastname && !$blnInformalFirstname)
                    {
                        $strSalutation = $GLOBALS['TL_LANG']['MSC']['haste_plus']['salutationGenericInformal'] . ' ' . $varEntity->lastname;
                    }
                    else
                    {
                        $strSalutation = $GLOBALS['TL_LANG']['MSC']['haste_plus']['salutationGenericInformal'];
                    }
                }
                elseif ($blnHasLastname && !$blnInformal)
                {
                    $strSalutation = $GLOBALS['TL_LANG']['MSC']['haste_plus']['salutation' . ($varEntity->gender == 'female' ? 'Female' : 'Male')];

                    if ($blnHasTitle)
                    {
                        $strSalutation .= ' ' . $varEntity->title;
                    }

                    $strSalutation = $strSalutation . ' ' . $varEntity->lastname;
                }
                else
                {
                    $strSalutation = $GLOBALS['TL_LANG']['MSC']['haste_plus']['salutationGeneric'];
                }
                break;
        }

        if ($strLanguage)
        {
            \Controller::loadLanguageFile('default', $GLOBALS['TL_LANGUAGE'], true);
        }

        return $strSalutation;
    }
}