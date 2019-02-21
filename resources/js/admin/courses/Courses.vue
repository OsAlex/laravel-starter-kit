<template>
    <div class="page_wrap_vue">
        <v-tabs color="primary" v-model="active">

            <v-tab class="white--text" href="#courses" ripple>
                Courses
            </v-tab>
            <v-tab class="white--text" href="#manage-categories" ripple>
                Manage Course Categories
            </v-tab>
            <v-tab class="white--text" href="#create" ripple>
                Create
            </v-tab>

            <v-tab-item value="courses">
                <v-card flat>
                    <v-card-text>
                        <course-lists></course-lists>
                    </v-card-text>
                </v-card>
            </v-tab-item>
            <v-tab-item value="manage-categories">
                <v-card flat>
                    <v-card-text>
                        <course-category-lists></course-category-lists>
                    </v-card-text>
                </v-card>
            </v-tab-item>
            <v-tab-item value="create">
                <v-card flat>
                    <v-card-text>
                        <course-create></course-create>
                    </v-card-text>
                </v-card>
            </v-tab-item>
        </v-tabs>
    </div>
</template>

<script>
    import CourseCategoryLists from './components/CourseCategoryLists.vue';
    import CourseCreate from './components/CourseCreate.vue';
    import CourseLists from './components/CourseLists.vue';
    export default {
        components: {
            // CourseCategoryLists,
            // CourseCreate,
            CourseLists
        },
        mounted() {
            console.log('pages.CourseManager.vue');

            const self = this;

            self.$store.commit('setBreadcrumbs',[
                {label:'Courses',name:''}
            ]);
        },
        data() {
            return {
                active: ''
            }
        },
        watch: {
            active(v) {
                console.log('active tab: ' + v);
            }
        },
        methods: {
        }
    }
</script>

<style scoped="">
    .finder_wrap {
        padding: 0 20px;
    }
</style>
