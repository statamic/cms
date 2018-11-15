export const intervalRegexp = /^({\s*(\-?\d+(\.\d+)?[\s*,\s*\-?\d+(\.\d+)?]*)\s*})|([\[\]])\s*(-Inf|\*|\-?\d+(\.\d+)?)\s*,\s*(\+?Inf|\*|\-?\d+(\.\d+)?)\s*([\[\]])$/;
export const anyIntervalRegexp = /({\s*(\-?\d+(\.\d+)?[\s*,\s*\-?\d+(\.\d+)?]*)\s*})|([\[\]])\s*(-Inf|\*|\-?\d+(\.\d+)?)\s*,\s*(\+?Inf|\*|\-?\d+(\.\d+)?)\s*([\[\]])/;

export const testInterval = function (count, interval) {
    /**
     * From the Symfony\Component\Translation\Interval Docs
     *
     * Tests if a given number belongs to a given math interval.
     *
     * An interval can represent a finite set of numbers:
     *
     *  {1,2,3,4}
     *
     * An interval can represent numbers between two numbers:
     *
     *  [1, +Inf]
     *  ]-1,2[
     *
     * The left delimiter can be [ (inclusive) or ] (exclusive).
     * The right delimiter can be [ (exclusive) or ] (inclusive).
     * Beside numbers, you can use -Inf and +Inf for the infinite.
     */

    if (typeof interval !== 'string') {
        throw 'Invalid interval: should be a string.';
    }

    interval = interval.trim();

    var matches = interval.match(intervalRegexp);
    if (!matches) {
        throw 'Invalid interval: ' + interval;
    }

    if (matches[2]) {
        var items = matches[2].split(',');
        for (var i = 0; i < items.length; i++) {
            if (parseInt(items[i], 10) === count) {
                return true;
            }
        }
    } else {
        // Remove falsy values.
        matches = matches.filter(function(match) {
            return !!match;
        });

        var leftDelimiter = matches[1];
        var leftNumber = convertNumber(matches[2]);
        if (leftNumber === Infinity) {
            leftNumber = -Infinity;
        }
        var rightNumber = convertNumber(matches[3]);
        var rightDelimiter = matches[4];

        return (leftDelimiter === '[' ? count >= leftNumber : count > leftNumber)
            && (rightDelimiter === ']' ? count <= rightNumber : count < rightNumber);
    }

    return false;
}

function convertNumber(str) {
    if (str === '-Inf') {
        return -Infinity;
    } else if (str === '+Inf' || str === 'Inf' || str === '*') {
        return Infinity;
    }
    return parseInt(str, 10);
}
