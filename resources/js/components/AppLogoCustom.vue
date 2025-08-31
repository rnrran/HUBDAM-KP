<template>
    <div>
        <img :src="logoSrc" alt="Olim" />
    </div>
</template>

<script>
export default {
    data() {
        return {
            logoSrc: require('@/assets/Kapota baru.png'), 
        };
    }
}
</script>