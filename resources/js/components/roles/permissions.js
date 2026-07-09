export function isVisible(permission, checkedPermissions) {
    return !permission.hidden_by.some((value) => checkedPermissions.includes(value));
}

export function visible(permissions, checkedPermissions) {
    return permissions.filter((permission) => isVisible(permission, checkedPermissions));
}
