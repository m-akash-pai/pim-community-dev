import React, {useEffect, useMemo, useState} from 'react';
import {Select2, Select2Configuration} from '../../../common';
import {fetchResult} from '../../../shared/fetch-result';
import {isOk} from '../../../shared/fetch-result/result';
import {useRoute} from '../../../shared/router';

type UserRole = {id: string; role: string; label: string};

type Props = {
    userRoleId: string;
    onChange: (userRoleId: string) => void;
};

export const UserRoleSelect = ({userRoleId: value, onChange}: Props) => {
    const [userRoles, setUserRoles] = useState<UserRole[]>([]);

    const route = useRoute('pim_user_user_role_rest_index');
    useEffect(() => {
        fetchResult<UserRole[], unknown>(route).then(result => {
            if (isOk(result)) {
                setUserRoles(result.value);
            }
        });
    }, []);

    const configuration: Select2Configuration = useMemo(
        () => ({
            data: userRoles.map(userRole => ({id: userRole.id, text: userRole.label})),
        }),
        [userRoles]
    );

    return <Select2 configuration={configuration} value={value} onChange={value => value && onChange(value)} />;
};
