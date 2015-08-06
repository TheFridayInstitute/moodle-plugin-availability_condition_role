YUI.add('moodle-availability_role-form', function (Y, NAME) {

/**
 * JavaScript for form editing group conditions.
 *
 * @module moodle-availability_role-form
 */
M.availability_role = M.availability_role || {};

/**
 * @class M.availability_role.form
 * @extends M.core_availability.plugin
 */
M.availability_role.form = Y.Object(M.core_availability.plugin);

/**
 * Groups available for selection (alphabetical order).
 *
 * @property groups
 * @type Array
 */
M.availability_role.form.groups = null;

/**
 * Initialises this plugin.
 *
 * @method initInner
 * @param {Array} groups Array of objects containing groupid => name
 */
M.availability_role.form.initInner = function(groups) {
    this.groups = groups;
};

M.availability_role.form.getNode = function(json) {
    // Create HTML structure.
    var html = '<label>' + M.util.get_string('title', 'availability_role') + ' <span class="availability-group">' +
            '<select name="id">' +
            '<option value="choose">' + M.util.get_string('choosedots', 'moodle') + '</option>' +
            '<option value="any">' + M.util.get_string('anyrole', 'availability_role') + '</option>';
    for (var i = 0; i < this.groups.length; i++) {
        var group = this.groups[i];
        // String has already been escaped using format_string.
        html += '<option value="' + group.id + '">' + group.name + '</option>';
    }
    html += '</select></span></label>';
    var node = Y.Node.create('<span>' + html + '</span>');

    // Set initial values (leave default 'choose' if creating afresh).
    if (json.creating === undefined) {
        if (json.id !== undefined &&
                node.one('select[name=id] > option[value=' + json.id + ']')) {
            node.one('select[name=id]').set('value', '' + json.id);
        } else if (json.id === undefined) {
            node.one('select[name=id]').set('value', 'any');
        }
    }

    // Add event handlers (first time only).
    if (!M.availability_role.form.addedEvents) {
        M.availability_role.form.addedEvents = true;
        var root = Y.one('#fitem_id_availabilityconditionsjson');
        root.delegate('change', function() {
            // Just update the form fields.
            M.core_availability.form.update();
        }, '.availability_role select');
    }

    return node;
};

M.availability_role.form.fillValue = function(value, node) {
    var selected = node.one('select[name=id]').get('value');
    if (selected === 'choose') {
        value.id = 'choose';
    } else if (selected !== 'any') {
        value.id = parseInt(selected, 10);
    }
};

M.availability_role.form.fillErrors = function(errors, node) {
    var value = {};
    this.fillValue(value, node);

    // Check group item id.
    if (value.id && value.id === 'choose') {
        errors.push('availability_role:error_selectrole');
    }
};


}, '@VERSION@', {"requires": ["base", "node", "event", "moodle-core_availability-form"]});
