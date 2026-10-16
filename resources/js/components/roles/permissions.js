export function isVisible(permission, checkedPermissions) {
    return !permission.hidden_by.some((value) => checkedPermissions.includes(value));
}

export function visible(permissions, checkedPermissions) {
    return permissions.filter((permission) => isVisible(permission, checkedPermissions));
}

export function allVisibleChecked(permissions, checkedPermissions) {
    return visible(permissions, checkedPermissions).every(
        (permission) => permission.checked && allVisibleChecked(permission.children, checkedPermissions),
    );
}

// The checked permissions are snapshotted by the caller. Visibility must not shift
// as we check things, otherwise checking a permission would hide the ones after it.
export function checkVisible(permissions, checkedPermissions) {
    visible(permissions, checkedPermissions).forEach((permission) => {
        permission.checked = true;
        checkVisible(permission.children, checkedPermissions);
    });
}

export function uncheckAll(permissions) {
    permissions.forEach((permission) => {
        permission.checked = false;
        uncheckAll(permission.children);
    });
}
