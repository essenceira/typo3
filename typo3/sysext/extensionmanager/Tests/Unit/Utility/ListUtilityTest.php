<?php

declare(strict_types=1);

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

namespace TYPO3\CMS\Extensionmanager\Tests\Unit\Utility;

use TYPO3\CMS\Core\EventDispatcher\NoopEventDispatcher;
use TYPO3\CMS\Core\Package\Package;
use TYPO3\CMS\Core\Package\PackageManager;
use TYPO3\CMS\Extensionmanager\Domain\Repository\ExtensionRepository;
use TYPO3\CMS\Extensionmanager\Utility\EmConfUtility;
use TYPO3\CMS\Extensionmanager\Utility\ListUtility;
use TYPO3\TestingFramework\Core\Unit\UnitTestCase;

final class ListUtilityTest extends UnitTestCase
{
    protected ListUtility $subject;

    protected function setUp(): void
    {
        parent::setUp();
        $this->subject = new ListUtility();
        $this->subject->injectEventDispatcher(new NoopEventDispatcher());
        $packageManagerMock = $this->getMockBuilder(PackageManager::class)
            ->disableOriginalConstructor()
            ->getMock();
        $packageManagerMock
                ->method('getActivePackages')
                ->willReturn([
                    'lang' => $this->getMockBuilder(Package::class)->disableOriginalConstructor()->getMock(),
                    'news' => $this->getMockBuilder(Package::class)->disableOriginalConstructor()->getMock(),
                    'felogin' => $this->getMockBuilder(Package::class)->disableOriginalConstructor()->getMock(),
                ]);
        $this->subject->injectPackageManager($packageManagerMock);
    }

    public static function getAvailableAndInstalledExtensionsDataProvider(): array
    {
        return [
            'same extension lists' => [
                [
                    'lang' => [],
                    'news' => [],
                    'felogin' => [],
                ],
                [
                    'lang' => ['installed' => true],
                    'news' => ['installed' => true],
                    'felogin' => ['installed' => true],
                ],
            ],
            'different extension lists' => [
                [
                    'lang' => [],
                    'news' => [],
                    'felogin' => [],
                ],
                [
                    'lang' => ['installed' => true],
                    'news' => ['installed' => true],
                    'felogin' => ['installed' => true],
                ],
            ],
            'different extension lists - set2' => [
                [
                    'lang' => [],
                    'news' => [],
                    'felogin' => [],
                    'em' => [],
                ],
                [
                    'lang' => ['installed' => true],
                    'news' => ['installed' => true],
                    'felogin' => ['installed' => true],
                    'em' => [],
                ],
            ],
            'different extension lists - set3' => [
                [
                    'lang' => [],
                    'fluid' => [],
                    'news' => [],
                    'felogin' => [],
                    'em' => [],
                ],
                [
                    'lang' => ['installed' => true],
                    'fluid' => [],
                    'news' => ['installed' => true],
                    'felogin' => ['installed' => true],
                    'em' => [],
                ],
            ],
        ];
    }

    /**
     * @test
     * @dataProvider getAvailableAndInstalledExtensionsDataProvider
     */
    public function getAvailableAndInstalledExtensionsTest(array $availableExtensions, array $expectedResult): void
    {
        self::assertEquals($expectedResult, $this->subject->getAvailableAndInstalledExtensions($availableExtensions));
    }

    public static function enrichExtensionsWithEmConfInformationDataProvider(): array
    {
        return [
            'simple key value array emconf' => [
                [
                    'lang' => ['property1' => 'oldvalue'],
                    'news' => [],
                    'felogin' => [],
                ],
                [
                    'property1' => 'property value1',
                ],
                [
                    'lang' => ['property1' => 'oldvalue', 'state' => 'stable'],
                    'news' => ['property1' => 'property value1', 'state' => 'stable'],
                    'felogin' => ['property1' => 'property value1', 'state' => 'stable'],
                ],
            ],
        ];
    }

    /**
     * @test
     * @dataProvider enrichExtensionsWithEmConfInformationDataProvider
     */
    public function enrichExtensionsWithEmConfInformation(array $extensions, array $emConf, array $expectedResult): void
    {
        $this->subject->injectExtensionRepository($this->getAccessibleMock(ExtensionRepository::class, ['findOneByExtensionKeyAndVersion', 'findHighestAvailableVersion'], [], '', false));
        $emConfUtilityMock = $this->getMockBuilder(EmConfUtility::class)->getMock();
        $emConfUtilityMock->method('includeEmConf')->willReturn($emConf);
        $this->subject->injectEmConfUtility($emConfUtilityMock);
        self::assertEquals($expectedResult, $this->subject->enrichExtensionsWithEmConfAndTerInformation($extensions));
    }
}
