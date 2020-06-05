<template>

    <div class="card update-release mb-5">
        <div class="flex justify-between mb-4">
            <div>
                <h1>{{ release.version }}</h1>
                <h5 class="date">Released on {{ release.date }}</h5>
            </div>
            <div v-if="showActions">
                <button v-if="release.type === 'current'" class="btn opacity-50" disabled>Current Version</button>
                <button v-else-if="release.latest" @click="updateToLatest()" class="btn">Update to Latest</button>
                <button v-else @click="installExplicitVersion(release.version)" class="btn">
                    <template v-if="release.type === 'upgrade'">Upgrade to</template>
                    <template v-if="release.type === 'downgrade'">Downgrade to</template>
                    {{ release.version }}
                </button>
            </div>
        </div>
        <div class="card-body">
            <div v-html="release.body"></div>
        </div>
    </div>

</template>

<script>
export default {

    props: {
        release: { type: Object, required: true },
        showActions: { type: Boolean }
    }

}
</script>
