import React, {useEffect, useMemo, useState} from 'react';
import {Select2, Select2Configuration} from '../../../common';
import {fetchResult} from '../../../shared/fetch-result';
import {isOk} from '../../../shared/fetch-result/result';
import {useRoute} from '../../../shared/router';

type UserGroup = {name: string; meta: {id: string; default: boolean}};

type Props = {
    userGroupId: string;
    onChange: (userGroupId: string) => void;
};

export const UserGroupSelect = ({userGroupId: value, onChange}: Props) => {
    const [userGroups, setUserGroups] = useState<UserGroup[]>([]);

    const route = useRoute('pim_user_user_group_rest_index');
    useEffect(() => {
        fetchResult<UserGroup[], unknown>(route).then(result => {
            if (isOk(result)) {
                setUserGroups(result.value);
            }
        });
    }, []);

    const configuration: Select2Configuration = useMemo(
        () => ({
            data: userGroups.map(userGroup => ({id: userGroup.meta.id, text: userGroup.name})),
            allowClear: true,
        }),
        [userGroups]
    );

    return <Select2 configuration={configuration} value={value} onChange={value => value && onChange(value)} />;
};
