<?php
declare(strict_types=1);
/**
 * @copyright 2017, Roeland Jago Douma <roeland@famdouma.nl>
 *
 * @author Roeland Jago Douma <roeland@famdouma.nl>
 *
 * @license GNU AGPL version 3 or any later version
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU Affero General Public License as
 * published by the Free Software Foundation, either version 3 of the
 * License, or (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU Affero General Public License for more details.
 *
 * You should have received a copy of the GNU Affero General Public License
 * along with this program.  If not, see <http://www.gnu.org/licenses/>.
 *
 */

namespace OCA\Password_Policy\AppInfo;

use OCA\Password_Policy\Capabilities;
use OCA\Password_Policy\Generator;
use OCA\Password_Policy\PasswordValidator;
use OCP\AppFramework\App;
use OCP\EventDispatcher\Event;
use OCP\EventDispatcher\IEventDispatcher;
use OCP\ILogger;
use OCP\Security\Events\GenerateSecurePasswordEvent;
use OCP\Security\Events\ValidatePasswordPolicyEvent;
use Symfony\Component\EventDispatcher\GenericEvent;

class Application extends App {
	public function __construct() {
		parent::__construct('password_policy');
		$container = $this->getContainer();

		$server = $container->getServer();
		/** @var IEventDispatcher $eventDispatcher */
		$eventDispatcher = $server->query(IEventDispatcher::class);

		/** register capabilities */
		$container->registerCapability(Capabilities::class);

		$eventDispatcher->addListener(
			ValidatePasswordPolicyEvent::class,
			function (Event $event) use ($container) {
				if (!($event instanceof ValidatePasswordPolicyEvent)) {
					return;
				}

				/** @var PasswordValidator $validator */
				$validator = $container->query(PasswordValidator::class);
				$validator->validate($event->getPassword());
			}
		);
		$eventDispatcher->addListener(
			GenerateSecurePasswordEvent::class,
			function (Event $event) use ($container) {
				if (!($event instanceof GenerateSecurePasswordEvent)) {
					return;
				}

				/** @var Generator */
				$generator = $container->query(Generator::class);
				$event->setPassword($generator->generate());
			}
		);

		// TODO: remove these two legacy event listeners
		$symfonyDispatcher = $server->getEventDispatcher();
		$symfonyDispatcher->addListener(
			'OCP\PasswordPolicy::validate',
			function (GenericEvent $event) use ($container) {
				/** @var ILogger $logger */
				$logger = $container->query(ILogger::class);
				$logger->debug('OCP\PasswordPolicy::validate is deprecated. Listen to ' . ValidatePasswordPolicyEvent::class . ' instead');

				/** @var PasswordValidator $validator */
				$validator = $container->query(PasswordValidator::class);
				$validator->validate($event->getSubject());
			}
		);
		$symfonyDispatcher->addListener(
			'OCP\PasswordPolicy::generate',
			function (GenericEvent $event) use ($container) {
				/** @var ILogger $logger */
				$logger = $container->query(ILogger::class);
				$logger->debug('OCP\PasswordPolicy::generate is deprecated. Listen to ' . GenerateSecurePasswordEvent::class . ' instead');

				/** @var Generator */
				$generator = $container->query(Generator::class);
				$event->setArgument('password', $generator->generate());
			}
		);
	}
}
