<?php

namespace App\Enums;

enum Achievement: string
{
    case FirstConnection = 'first-connection';
    case GettingStarted = 'getting-started';
    case SocialButterfly = 'social-butterfly';
    case SuperConnector = 'super-connector';
    case Legend = 'legend';
    case FiveStar = 'five-star';
    case CrowdPleaser = 'crowd-pleaser';
    case Curious = 'curious';
    case EarlyTester = 'early-tester';
    case AllWiredUp = 'all-wired-up';
    case Popular = 'popular';
    case NightOwl = 'night-owl';
    case EarlyBird = 'early-bird';
    case Prolific = 'prolific';
    case Completionist = 'completionist';

    public function label(): string
    {
        return match ($this) {
            self::FirstConnection => 'First Connection',
            self::GettingStarted => 'Getting Started',
            self::SocialButterfly => 'Social Butterfly',
            self::SuperConnector => 'Super Connector',
            self::Legend => 'Legend',
            self::FiveStar => 'Five Star',
            self::CrowdPleaser => 'Crowd Pleaser',
            self::Curious => 'Curious',
            self::EarlyTester => 'Early Tester',
            self::AllWiredUp => 'All Wired Up',
            self::Popular => 'Popular',
            self::NightOwl => 'Night Owl',
            self::EarlyBird => 'Early Bird',
            self::Prolific => 'Prolific',
            self::Completionist => 'Completionist',
        };
    }

    public function description(): string
    {
        return match ($this) {
            self::FirstConnection => 'Made your first connection.',
            self::GettingStarted => 'Reached 3 connections.',
            self::SocialButterfly => 'Reached 10 connections.',
            self::SuperConnector => 'Reached 25 connections.',
            self::Legend => 'Reached 50 connections.',
            self::FiveStar => 'One of your icebreaker answers earned a 5-star rating.',
            self::CrowdPleaser => 'Averaged 4.5+ stars across 5 or more rated answers.',
            self::Curious => 'Scanned 10 people to break the ice.',
            self::EarlyTester => 'Joined before the event began — thanks for helping test!',
            self::AllWiredUp => 'Linked GitHub, X, Bluesky, and LinkedIn.',
            self::Popular => 'Got scanned by 10 different people.',
            self::NightOwl => 'Confirmed a meeting after 9pm.',
            self::EarlyBird => 'Confirmed a meeting before 9am.',
            self::Prolific => 'Answered 25 icebreakers.',
            self::Completionist => 'Earned every other badge.',
        };
    }

    public function icon(): string
    {
        return match ($this) {
            self::FirstConnection => '🤝',
            self::GettingStarted => '🌱',
            self::SocialButterfly => '🦋',
            self::SuperConnector => '🌟',
            self::Legend => '🏆',
            self::FiveStar => '⭐',
            self::CrowdPleaser => '😄',
            self::Curious => '🔍',
            self::EarlyTester => '🧪',
            self::AllWiredUp => '🔗',
            self::Popular => '🧲',
            self::NightOwl => '🦉',
            self::EarlyBird => '🐦',
            self::Prolific => '📚',
            self::Completionist => '🎖️',
        };
    }
}
