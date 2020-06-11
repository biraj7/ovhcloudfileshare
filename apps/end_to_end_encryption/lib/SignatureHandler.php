<?php
/**
 * @copyright Copyright (c) 2017 Bjoern Schiessle <bjoern@schiessle.org>
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


namespace OCA\EndToEndEncryption;


use OC\Security\IdentityProof\Manager;

/**
 * Class SignatureHandler
 *
 * signs users public keys and manage system keys
 *
 * @package OCA\EndToEndEncryption
 */
class SignatureHandler {

	/** @var Manager */
	private $identityProofManager;

	/** @var  int the signature is 20 years (7300 days) valid */
	private $validity = 7300;

	/**
	 * SignatureHandler constructor.
	 *
	 * @param Manager $identityProofManager
	 */
	public function __construct(Manager $identityProofManager) {
		$this->identityProofManager = $identityProofManager;
	}

	/**
	 * @param string $csr
	 * @return string signed certificate
	 * @throws \BadMethodCallException
	 * @throws \RuntimeException
	 */
	public function sign($csr) {
		$systemKeys = $this->identityProofManager->getSystemKey();
		$signedCertificate = openssl_csr_sign($csr, null, $systemKeys->getPrivate(), $this->validity);
		if ($signedCertificate === false) {
			throw new \BadMethodCallException('could not sign the CSR, please make sure to submit a valid CSR');
		}
		openssl_x509_export($signedCertificate, $result);
		return $result;
	}

	/**
	 * return the public key of the instance wide key-pair
	 *
	 * @return string
	 * @throws \RuntimeException
	 */
	public function getPublicServerKey() {
		$publicKey = $this->identityProofManager->getSystemKey()->getPublic();
		return $publicKey;
	}

}
