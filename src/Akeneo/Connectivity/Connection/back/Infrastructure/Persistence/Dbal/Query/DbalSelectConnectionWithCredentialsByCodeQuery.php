<?php

declare(strict_types=1);

namespace Akeneo\Connectivity\Connection\Infrastructure\Persistence\Dbal\Query;

use Akeneo\Connectivity\Connection\Domain\Settings\Model\Read\ConnectionWithCredentials;
use Akeneo\Connectivity\Connection\Domain\Settings\Persistence\Query\SelectConnectionWithCredentialsByCodeQuery;
use Doctrine\DBAL\Connection as DbalConnection;

/**
 * @author Romain Monceau <romain@akeneo.com>
 * @copyright 2019 Akeneo SAS (http://www.akeneo.com)
 * @license http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 */
class DbalSelectConnectionWithCredentialsByCodeQuery implements SelectConnectionWithCredentialsByCodeQuery
{
    /** @var DbalConnection */
    private $dbalConnection;

    public function __construct(DbalConnection $dbalConnection)
    {
        $this->dbalConnection = $dbalConnection;
    }

    public function execute(string $code): ?ConnectionWithCredentials
    {
        $selectSQL = <<<SQL
SELECT c.code, c.label, c.flow_type, c.image, c.client_id, client.random_id, client.secret, u.username, urole.role_id, ugroup.group_id
FROM akeneo_connectivity_connection c
INNER JOIN pim_api_client client on c.client_id = client.id
INNER JOIN oro_user u on c.user_id = u.id
INNER JOIN oro_user_access_role urole on u.id = urole.user_id
INNER JOIN oro_user_access_group ugroup on u.id = ugroup.user_id
WHERE code = :code
SQL;
        $dataRow = $this->dbalConnection->executeQuery($selectSQL, ['code' => $code])->fetch();
        if (!$dataRow) {
            return null;
        }

        return new ConnectionWithCredentials(
            $dataRow['code'],
            $dataRow['label'],
            $dataRow['flow_type'],
            $dataRow['client_id'].'_'.$dataRow['random_id'],
            $dataRow['secret'],
            $dataRow['username'],
            null,
            $dataRow['image'],
            $dataRow['role_id'],
            $dataRow['group_id'],
        );
    }
}
