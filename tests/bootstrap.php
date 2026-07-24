<?php

require_once dirname(__DIR__) . '/vendor/autoload.php';

require_once __DIR__ . '/Stubs/xPDOSimpleObject.php';
require_once __DIR__ . '/Stubs/modX.php';
require_once __DIR__ . '/Stubs/FakeQuery.php';
require_once __DIR__ . '/Stubs/modUserProfile.php';
require_once __DIR__ . '/Stubs/modUser.php';
require_once __DIR__ . '/Stubs/modTemplate.php';
require_once __DIR__ . '/Stubs/modParser.php';
require_once __DIR__ . '/Stubs/FakeRegister.php';
require_once __DIR__ . '/Stubs/FakeRegistry.php';
require_once __DIR__ . '/Stubs/modMail.php';
require_once __DIR__ . '/Stubs/xPDO.php';
require_once __DIR__ . '/Stubs/sxSubscriber.php';
require_once __DIR__ . '/Stubs/sxQueue.php';
require_once __DIR__ . '/Stubs/FakeMail.php';
require_once __DIR__ . '/Stubs/FakeModX.php';
require_once __DIR__ . '/Stubs/modProcessor.php';

require_once dirname(__DIR__) . '/core/components/sendex/model/sendex/sxnewsletter.class.php';
require_once dirname(__DIR__) . '/core/components/sendex/model/sendex/sxqueuesender.class.php';
require_once __DIR__ . '/Support/TestableNewsletter.php';

require_once dirname(__DIR__) . '/core/components/sendex/processors/mgr/newsletter/subscriber/create.class.php';
require_once dirname(__DIR__) . '/core/components/sendex/processors/mgr/newsletter/subscriber/remove.class.php';
