<?php

use PHPUnit\Framework\TestCase;

require_once dirname(__DIR__, 2) . '/core/components/sendex/model/sendex/sxmodxcompat.class.php';
require_once dirname(__DIR__, 2) . '/core/components/sendex/model/sendex/sxuserprofile.class.php';

class ModxCompatTest extends TestCase
{
    public function testGetMailUsesServicesContainerOnModx3()
    {
        $mail = new FakeMail();
        $modx = new FakeModX();
        $modx->services = new FakeModxServices(array('mail' => $mail));

        $this->assertSame($mail, sxModxCompat::getMail($modx));
    }

    public function testGetMailFallsBackToGetService()
    {
        $modx = new FakeModX();
        unset($modx->services);

        $mail = sxModxCompat::getMail($modx);

        $this->assertInstanceOf(FakeMail::class, $mail);
    }

    public function testMailConstUsesModMailWhenAvailable()
    {
        $this->assertSame(modMail::MAIL_BODY, sxModxCompat::mailConst('BODY'));
    }

    public function testIsActiveUserUsesGetNotProperty()
    {
        $modx = new FakeModX();
        $user = new modUser($modx);
        $user->set('active', 1);

        $this->assertTrue(sxUserProfile::isActiveUser($user));

        $user->set('active', 0);
        $this->assertFalse(sxUserProfile::isActiveUser($user));
    }

    public function testProfileArrayFallsBackToModUserProfile()
    {
        $modx = new FakeModX();
        $user = new modUser($modx);
        $user->set('id', 5);

        $profile = new modUserProfile($modx);
        $profile->set('internalKey', 5);
        $profile->set('email', 'profile@example.com');
        $modx->userProfiles[5] = $profile;

        $data = sxUserProfile::profileArray($user, $modx);

        $this->assertIsArray($data);
        $this->assertSame('profile@example.com', $data['email']);
    }

    public function testAuthenticatedPlaceholdersDelegatesToMerge()
    {
        $modx = new FakeModX();
        $user = new modUser($modx);
        $user->set('id', 1);
        $user->set('username', 'alice');

        $out = sxUserProfile::authenticatedPlaceholders(
            $modx,
            $user,
            array('id' => 9, 'name' => 'Newsletter')
        );

        $this->assertSame(9, $out['id']);
        $this->assertSame('alice', $out['username']);
        $this->assertSame('Newsletter', $out['name']);
    }
}
