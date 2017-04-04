<?php

namespace AppBundle\DataFixtures\ORM;

use Doctrine\Common\DataFixtures\AbstractFixture;
use Doctrine\Common\Persistence\ObjectManager;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Note! Files should be in 'web/uploads/settings' folder
 *
 * Class LoadSetting
 * @package Axioma\SettingsBundle\FixtureExample
 */
class LoadSetting extends AbstractFixture implements ContainerAwareInterface
{
    /**
     * @var \Axioma\SettingsBundle\Core\Repository
     */
    private $repository;

    public function setContainer(ContainerInterface $container = null)
    {
        $this->repository = $container->get('axioma.settings.repository');
    }

    /**
     * {@inheritDoc}
     */
    public function load(ObjectManager $manager)
    {
        foreach ($this->getSettings() as $option) {
            if (!array_key_exists('type', $option)) {
                $option['type'] = 'text';
            }

            if (!array_key_exists('description', $option)) {
                $option['description'] = null;
            }

            if (!array_key_exists('is_visible', $option)) {
                $option['is_visible'] = true;
            }

            $setting = $this->repository->create();
            $setting->setKey($option['key']);
            $setting->setDescription($option['description']);
            $setting->setType($option['type']);
            $setting->setIsVisible($option['is_visible']);
            $setting->setIsSystem(true);

            switch ($option['type']) {
                case 'file':
                    $setting->setValue($option['filename']);

                    break;
                case 'select':
                    $setting->setValue(
                        json_encode(
                            array(
                                'options' => $option['options'],
                                'chosen_option' => array(
                                    $option['chosen_option'] => $option['options'][$option['chosen_option']]
                                )
                            )
                        )
                    );

                    break;
                default:
                    $setting->setValue($option['value']);
            }

            $manager->persist($setting);
        }

        $manager->flush();
    }

    /**
     * Default Setting type is "text".
     *
     * @return array
     */
    public function getSettings()
    {
        return array(
            array(
                'key'         => 'keyword-text-type',
                'description' => 'Text option description',
                'type'        => 'text',
                'value'       => '24',
            ),
            array(
                'key'         => 'keyword-invisible',
                'value'       => '24',
                'is_visible'  => false,
            ),
            array(
                'key'         => 'keyword-wysiwyg',
                'type'        => 'wysiwyg',
                'value'       => '<h1>header</h1><p>body</p>',
            ),
            array(
                'key'         => 'keyword-file-type',
                'description' => 'File option description',
                'type'        => 'file',
                'filename'    => 'image.jpg',
            ),
            array(
                'key'           => 'keyword-select-type',
                'description'   => 'Select option description',
                'type'          => 'select',
                'options'       => array(
                    'value1' => 'text1',
                    'value2' => 'text2',
                    'value3' => 'text3',
                    'value4' => 'text4',
                ),
                'chosen_option' => 'value2',
            ),
        );
    }
}
