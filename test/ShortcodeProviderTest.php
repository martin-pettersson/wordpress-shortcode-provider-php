<?php

/*
 * Copyright (c) 2025 Martin Pettersson
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace N7e\WordPress;

use N7e\Configuration\ConfigurationInterface;
use N7e\DependencyInjection\ContainerBuilderInterface;
use N7e\DependencyInjection\ContainerInterface;
use phpmock\phpunit\PHPMock;
use PHPUnit\Framework\Attributes\Before;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

#[CoversClass(ShortcodeProvider::class)]
class ShortcodeProviderTest extends TestCase
{
    use PHPMock;

    private ShortcodeProvider $provider;
    private MockObject $containerMock;
    private MockObject $configurationMock;

    #[Before]
    public function setUp(): void
    {
        $this->containerMock = $this->getMockBuilder(ContainerInterface::class)->getMock();
        $this->configurationMock = $this->getMockBuilder(ConfigurationInterface::class)->getMock();
        $this->provider = new ShortcodeProvider();

        $this->containerMock->method('get')
            ->with(ConfigurationInterface::class)
            ->willReturn($this->configurationMock);
    }

    #[Test]
    public function shouldNotConfigureContainerBuilder(): void
    {
        $containerBuilderMock = $this->getMockBuilder(ContainerBuilderInterface::class)->getMock();
        $containerBuilderMock->expects($this->never())->method($this->anything());

        $this->provider->configure($containerBuilderMock);
    }

    #[Test]
    public function shouldNotRegisterAnyShortcodesIfConfigurationIsEmpty(): void
    {
        $this->configurationMock
            ->expects($this->once())
            ->method('get')
            ->with('shortcodes', [])
            ->willReturn([]);
        $this->containerMock->expects($this->never())->method('construct');

        $this->provider->load($this->containerMock);
    }

    #[Test]
    public function shouldRegisterShortcodeClassesFromConfiguration(): void
    {
        $shortcodeMock = $this->getMockBuilder(Shortcode::class)->getMock();

        $shortcodeMock
            ->expects($this->once())
            ->method('tag');
        $this->configurationMock
            ->expects($this->once())
            ->method('get')
            ->with('shortcodes', [])
            ->willReturn(['class']);
        $this->containerMock
            ->expects($this->once())
            ->method('construct')
            ->with('class')
            ->willReturn($shortcodeMock);
        $this->getFunctionMock(__NAMESPACE__, 'add_shortcode')
            ->expects($this->once())
            ->with($this->anything(), $this->anything());

        $this->provider->load($this->containerMock);
    }
}
