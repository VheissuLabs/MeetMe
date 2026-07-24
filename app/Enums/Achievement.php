<?php

namespace App\Enums;

enum Achievement: string
{
    case FirstConnection = 'first-connection';
    case GettingStarted = 'getting-started';
    case SocialButterfly = 'social-butterfly';
    case SuperConnector = 'super-connector';
    case FiveStar = 'five-star';
    case EarlyTester = 'early-tester';

    public function label(): string
    {
        return match ($this) {
            self::FirstConnection => 'First Connection',
            self::GettingStarted => 'Getting Started',
            self::SocialButterfly => 'Social Butterfly',
            self::SuperConnector => 'Super Connector',
            self::FiveStar => 'Five Star',
            self::EarlyTester => 'Early Tester',
        };
    }

    public function description(): string
    {
        return match ($this) {
            self::FirstConnection => 'Made your first connection.',
            self::GettingStarted => 'Reached 3 connections.',
            self::SocialButterfly => 'Reached 10 connections.',
            self::SuperConnector => 'Reached 25 connections.',
            self::FiveStar => 'One of your icebreaker answers earned a 5-star rating.',
            self::EarlyTester => 'Joined before the event began — thanks for helping test!',
        };
    }

    public function icon(): string
    {
        return match ($this) {
            self::FirstConnection => '🤝',
            self::GettingStarted => '🌱',
            self::SocialButterfly => '🦋',
            self::SuperConnector => '🌟',
            self::FiveStar => '⭐',
            self::EarlyTester => '🧪',
        };
    }
}
