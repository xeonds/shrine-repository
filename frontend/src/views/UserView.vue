<template>
  <div>
    <div id="nav">
      <ul>
        <li>{{ this.$route.params.user_id }}</li>
        <li><router-link to="analysis">Analysis</router-link></li>
        <li><router-link to="meta">Meta</router-link></li>
        <li><router-link to="advanced">Advanced</router-link></li>
      </ul>
    </div>
  </div>
</template>

<style scoped>
</style>

<script>
export default {
  name: "UserInfo",
  data: function () {
    return {};
  },
  created: function () {},
  methods: {},
};
</script>