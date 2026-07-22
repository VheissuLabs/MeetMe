<?php

// @formatter:off
// phpcs:ignoreFile
/**
 * A helper file for your Eloquent Models
 * Copy the phpDocs from this file to the correct Model,
 * And remove them from this file, to prevent double declarations.
 *
 * @author Barry vd. Heuvel <barryvdh@gmail.com>
 */


namespace App\Models{
/**
 * @property string $id
 * @property string $question
 * @property \Carbon\CarbonImmutable|null $created_at
 * @property \Carbon\CarbonImmutable|null $updated_at
 * @method static \Database\Factories\IcebreakerQuestionFactory factory($count = null, $state = [])
 * @method static \Illuminate\Database\Eloquent\Builder<static>|IcebreakerQuestion newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|IcebreakerQuestion newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|IcebreakerQuestion query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|IcebreakerQuestion whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|IcebreakerQuestion whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|IcebreakerQuestion whereQuestion($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|IcebreakerQuestion whereUpdatedAt($value)
 * @mixin \Eloquent
 */
	#[\AllowDynamicProperties]
	class IdeHelperIcebreakerQuestion {}
}

namespace App\Models{
/**
 * @property string $id
 * @property int $initiator_id
 * @property int $recipient_id
 * @property string|null $icebreaker_question_id
 * @property string $question
 * @property string|null $answer
 * @property int|null $rating
 * @property \Carbon\CarbonImmutable|null $answer_redacted_at
 * @property \App\Enums\MeetingStatus $status
 * @property string $pair_key
 * @property \Carbon\CarbonImmutable|null $answered_at
 * @property \Carbon\CarbonImmutable|null $resolved_at
 * @property \Carbon\CarbonImmutable|null $created_at
 * @property \Carbon\CarbonImmutable|null $updated_at
 * @property-read \App\Models\IcebreakerQuestion|null $icebreakerQuestion
 * @property-read \App\Models\User $initiator
 * @property-read \App\Models\User $recipient
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Meeting confirmed()
 * @method static \Database\Factories\MeetingFactory factory($count = null, $state = [])
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Meeting involving(\App\Models\User $user)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Meeting newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Meeting newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Meeting query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Meeting whereAnswer($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Meeting whereAnswerRedactedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Meeting whereAnsweredAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Meeting whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Meeting whereIcebreakerQuestionId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Meeting whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Meeting whereInitiatorId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Meeting wherePairKey($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Meeting whereQuestion($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Meeting whereRating($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Meeting whereRecipientId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Meeting whereResolvedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Meeting whereStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Meeting whereUpdatedAt($value)
 * @mixin \Eloquent
 */
	#[\AllowDynamicProperties]
	class IdeHelperMeeting {}
}

namespace App\Models{
/**
 * @property int $id
 * @property int $user_id
 * @property \App\Enums\SocialProvider $provider
 * @property string $provider_id
 * @property string|null $username
 * @property string|null $avatar_url
 * @property \Carbon\CarbonImmutable|null $created_at
 * @property \Carbon\CarbonImmutable|null $updated_at
 * @property-read \App\Models\User $user
 * @method static \Database\Factories\SocialAccountFactory factory($count = null, $state = [])
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SocialAccount newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SocialAccount newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SocialAccount query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SocialAccount whereAvatarUrl($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SocialAccount whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SocialAccount whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SocialAccount whereProvider($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SocialAccount whereProviderId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SocialAccount whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SocialAccount whereUserId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SocialAccount whereUsername($value)
 * @mixin \Eloquent
 */
	#[\AllowDynamicProperties]
	class IdeHelperSocialAccount {}
}

namespace App\Models{
/**
 * @property int $id
 * @property string $name
 * @property string $email
 * @property \Carbon\CarbonImmutable|null $email_verified_at
 * @property string|null $password
 * @property string|null $remember_token
 * @property \Carbon\CarbonImmutable|null $created_at
 * @property \Carbon\CarbonImmutable|null $updated_at
 * @property string|null $two_factor_secret
 * @property string|null $two_factor_recovery_codes
 * @property \Carbon\CarbonImmutable|null $two_factor_confirmed_at
 * @property string $qr_token
 * @property string|null $bluesky_handle
 * @property string|null $avatar_url
 * @property \App\Enums\AvatarSource $avatar_source
 * @property string|null $pronouns
 * @property bool $email_visible
 * @property string|null $x_username
 * @property-read \App\Models\SocialAccount|null $githubAccount
 * @property-read \Illuminate\Notifications\DatabaseNotificationCollection<int, \Illuminate\Notifications\DatabaseNotification> $notifications
 * @property-read int|null $notifications_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \Laravel\Passkeys\Passkey> $passkeys
 * @property-read int|null $passkeys_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\SocialAccount> $socialAccounts
 * @property-read int|null $social_accounts_count
 * @property-read \App\Models\SocialAccount|null $xAccount
 * @method static \Database\Factories\UserFactory factory($count = null, $state = [])
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User whereAvatarSource($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User whereAvatarUrl($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User whereBlueskyHandle($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User whereEmail($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User whereEmailVerifiedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User whereEmailVisible($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User wherePassword($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User wherePronouns($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User whereQrToken($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User whereRememberToken($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User whereTwoFactorConfirmedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User whereTwoFactorRecoveryCodes($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User whereTwoFactorSecret($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User whereXUsername($value)
 * @mixin \Eloquent
 */
	#[\AllowDynamicProperties]
	class IdeHelperUser {}
}

