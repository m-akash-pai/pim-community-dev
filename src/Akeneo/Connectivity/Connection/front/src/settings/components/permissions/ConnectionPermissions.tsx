import React, {FC} from 'react';
import {FormGroup, HelperLink, Section, SmallHelper} from '../../../common';
import {Translate, useTranslate} from '../../../shared/translate';
import {UserGroupSelect} from './UserGroupSelect';
import {UserRoleSelect} from './UserRoleSelect';

type Props = {
    code: string;
    label: string;
    userRoleId: string;
    userGroupId: string;
};

export const ConnectionPermissions: FC<Props> = ({code, label, userRoleId, userGroupId}: Props) => {
    const translate = useTranslate();

    return (
        <>
            <Section
                title={
                    <Translate
                        id='akeneo_connectivity.connection.edit_connection.permissions.title'
                        placeholders={{label}}
                    />
                }
            />
            <SmallHelper>
                <Translate id='akeneo_connectivity.connection.edit_connection.permissions.helper.message' />
                &nbsp;
                <HelperLink
                    href={translate('akeneo_connectivity.connection.edit_connection.permissions.helper.link_url')}
                    target='_blank'
                    rel='noopener noreferrer'
                >
                    <Translate id='akeneo_connectivity.connection.edit_connection.permissions.helper.link' />
                </HelperLink>
            </SmallHelper>

            <br />

            <FormGroup label='akeneo_connectivity.connection.connection.user_role_id'>
                <UserRoleSelect userRoleId={userRoleId} onChange={userRoleId => console.log(userRoleId)} />
            </FormGroup>

            <FormGroup label='akeneo_connectivity.connection.connection.user_role_id'>
                <UserGroupSelect userGroupId={userGroupId} onChange={userGroupId => console.log(userGroupId)} />
            </FormGroup>
        </>
    );
};
