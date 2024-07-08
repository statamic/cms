import { computed, MaybeRef, Ref, toValue } from 'vue';

type Props = {
    value: Ref<any>
    limit: MaybeRef<number>
}

export default function useLengthLimiter({ value, limit }: Props) {
    const currentLength = computed(
        () => value.value
            ? value.value.length
            : 0
    )

    const limitReached = computed(
        () => currentLength.value > toValue(limit)
    )

    const limitIndicatorColor = computed(
        () => limitReached.value ? 'text-red-500' : 'text-gray'
    )

    return {
        limitReached,
        currentLength,
        limitIndicatorColor,
    }
}