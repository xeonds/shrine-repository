<template>
  <div class="container-fluid" style="padding: 0">
    <!-- Nav tabs -->
    <nav
      class="
        navbar navbar-expand
        bg-light
        navbar-light
        justify-content-start justify-content-sm-center
      "
    >
      <div class="navbar justify-content-sm-center">
        <ul class="navbar-nav">
          <li class="nav-item">
            <router-link class="nav-link" to="./">Analysis</router-link>
          </li>
          <li class="nav-item">
            <router-link class="nav-link" to="meta">Meta</router-link>
          </li>
          <li class="nav-item">
            <router-link class="nav-link" to="advanced">Advanced</router-link>
          </li>
          <li class="nav-item">
            <router-link class="nav-link" to="/">Home</router-link>
          </li>
        </ul>
      </div>
    </nav>
    <!-- Main view -->
    <div
      id="main"
      class="d-flex flex-column align-items-center tab-content w-100"
      style="min-height: 85vh"
    >
      <router-view></router-view>
    </div>
  </div>
  <div
    id="footer"
    class="
      d-flex
      flex-column
      align-items-center
      justify-content-center
      bg-light
    "
    style="padding: 2rem"
  >
    <p>&copy; 2021 <span class="text-primary">|</span> xeonds</p>
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