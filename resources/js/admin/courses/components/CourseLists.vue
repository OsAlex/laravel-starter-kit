<template>
    <div class="component-wrap">

        <!-- filters -->
        <v-card>
            <v-card-text>
                <v-layout row wrap>
                    <v-flex xs12>
                        <v-text-field prepend-icon="search" box label="Filter By Name or Extension" v-model="filters.name"></v-text-field>
                    </v-flex>
                    <!--<v-flex xs12>-->
                        <!--Show Only:-->
                    <!--</v-flex>-->
                    <!--<v-flex xs4 md2 v-for="(category,i) in filters.courseCategoriesHolder" :key="i">-->
                        <!--<v-checkbox v-bind:label="category.name" v-model="filters.courseCategoryId[category.id]"></v-checkbox>-->
                    <!--</v-flex>-->
                </v-layout>
            </v-card-text>
        </v-card>

        <!-- categories table -->
        <v-data-table
            v-bind:headers="headers"
            v-bind:pagination.sync="pagination"
            :items="items"
            :total-items="totalItems"
            class="elevation-1">
            <!--<template slot="headerCell" slot-scope="props">-->
                <!--<span v-if="props.header.value=='thumb'">-->
                    <!--<v-icon>panorama</v-icon> {{ props.header.text }}-->
                <!--</span>-->
                <!--<span v-else-if="props.header.value=='category'">-->
                    <!--<v-icon>folder</v-icon> {{ props.header.text }}-->
                <!--</span>-->
                <!--<span v-else-if="props.header.value=='created_at'">-->
                    <!--<v-icon>date_range</v-icon> {{ props.header.text }}-->
                <!--</span>-->
                <!--<span v-else>{{ props.header.text }}</span>-->
            <!--</template>-->
            <template slot="items" slot-scope="props">
                <td class="wask_td_action">
                    <v-btn @click="showDialog('course_show',props.item)" icon small>
                        <v-icon dark class="blue--text">search</v-icon>
                    </v-btn>
                    <v-btn @click="trash(props.item)" icon small>
                        <v-icon class="red--text">delete</v-icon>
                    </v-btn>
                </td>
                <td>
                    <v-avatar
                        tile
                        :size="'50px'"
                        class="grey lighten-4">
                        <img :src="getFullUrl(props.item,50,'fit')"/>
                    </v-avatar>
                </td>
                <td>{{ props.item.name }}</td>
                <td>{{ $appFormatters.formatByteToMB(props.item.size).toString() + ' MB' }}</td>
                <td>{{ props.item.category.name }}</td>
                <td>{{ $appFormatters.formatDate(props.item.created_at) }}</td>
            </template>
        </v-data-table>
        <!-- /categories table -->

        <!-- view course dialog -->
        <v-dialog v-model="dialogs.view.show" fullscreen :laze="false" transition="dialog-bottom-transition" :overlay=false>
            <v-card>
                <v-toolbar class="primary">
                    <v-btn icon @click.native="dialogs.view.show = false" dark>
                        <v-icon>close</v-icon>
                    </v-btn>
                    <v-toolbar-title class="white--text">{{dialogs.view.course.name}}</v-toolbar-title>
                    <v-spacer></v-spacer>
                    <v-toolbar-items>
                        <v-btn dark flat @click.native="downloadCourse(dialogs.view.course)">
                            Download
                            <v-icon right dark>course_download</v-icon></v-btn>
                    </v-toolbar-items>
                    <v-toolbar-items>
                        <v-btn dark flat @click.native="trash(dialogs.view.course)">
                            Delete
                            <v-icon right dark>delete</v-icon></v-btn>
                    </v-toolbar-items>
                </v-toolbar>
                <v-card-text>
                    <div class="course_view_popup">
                        <div class="course_view_popup_link">
                            <v-text-field flat disabled :value="getFullUrl(dialogs.view.course)"></v-text-field>
                        </div>
                        <img :src="getFullUrl(dialogs.view.course)"/>
                    </div>
                </v-card-text>
            </v-card>
        </v-dialog>

    </div>
</template>

<script>
    import _ from 'lodash';

    export default {
        components: {},
        data() {
            return {
                headers: [
                    { text: 'Action', value: false, align: 'left', sortable: false },
                    { text: 'Thumb', value: 'thumb', align: 'left', sortable: false },
                    { text: 'Name', value: 'name', align: 'left', sortable: false },
                    { text: 'Size', value: 'size', align: 'left', sortable: false },
                    { text: 'Found In', value: 'category', align: 'left', sortable: false },
                    { text: 'Date Created', value: 'created_at', align: 'left', sortable: false },
                ],
                items: [],
                totalItems: 0,
                pagination: {
                    rowsPerPage: 10
                },

                filters: {
                    name: '',
                    selectedCategoryIds: '',
                    courseCategoryId: [],
                    courseCategoriesHolder: []
                },

                dialogs: {
                    view: {
                        course: {},
                        show: false
                    },
                }
            }
        },
        mounted() {
            console.log('pages.courses.components.CourseLists.vue');

            const self = this;

            self.$eventBus.$on(['FILE_DELETED','FILE_UPLOADED'], function () {
                self.loadCourses(()=>{});
            });
        },
        watch: {
            'filters.courseCategoryId':_.debounce(function(v) {

                let selected = [];

                _.each(v,(v,k)=>{
                    if(v) selected.push(k);
                });

                this.filters.selectedCategoryIds = selected.join(',');
            },500),
            'filters.selectedCategoryIds'(v) {
                this.loadCourses(()=>{});
            },
            'filters.name':_.debounce(function(v) {
                this.loadCourses(()=>{});
            },500),
            'pagination.page':function(){
                this.loadCourses(()=>{});
            },
            'pagination.rowsPerPage':function(){
                this.loadCourses(()=>{});
            },
        },
        methods: {
            getFullUrl(course, width, action) {

                let w = width || 4000;
                let act = action || 'resize';

                return LSK_APP.APP_URL +`/courses/`+course.id+`/preview?w=`+w+`&action=`+act;
            },
            downloadCourse(course) {
                window.open(LSK_APP.APP_URL + '/courses/'+course.id+'/download?course_token='+course.course_token);
            },
            showDialog(dialog, data) {

                const self = this;

                switch (dialog){
                    case 'course_show':
                        self.dialogs.view.course = data;
                        setTimeout(()=>{
                            self.dialogs.view.show = true;
                        },500);
                        break;
                }
            },
            trash(course) {
                const self = this;

                self.$store.commit('showDialog',{
                    type: "confirm",
                    title: "Confirm Deletion",
                    message: "Are you sure you want to delete this course?",
                    okCb: ()=>{

                        axios.delete('/admin/courses/' + course.id).then(function(response) {

                            self.$store.commit('showSnackbar',{
                                message: response.data.message,
                                color: 'success',
                                duration: 3000
                            });

                            self.$eventBus.$emit('FILE_DELETED');

                            // maybe the action took place from view course
                            // lets close it.
                            self.dialogs.view.show = false;

                        }).catch(function (error) {
                            if (error.response) {
                                self.$store.commit('showSnackbar',{
                                    message: error.response.data.message,
                                    color: 'error',
                                    duration: 3000
                                });
                            } else if (error.request) {
                                console.log(error.request);
                            } else {
                                console.log('Error', error.message);
                            }
                        });
                    },
                    cancelCb: ()=>{
                        console.log("CANCEL");
                    }
                });
            },
            loadCourseCategorys(cb) {

                const self = this;

                let params = {
                    paginate: 'no'
                };

                axios.get('/admin/course-categories',{params: params}).then(function(response) {
                    self.filters.courseCategoriesHolder = response.data.data;
                    cb();
                });
            },
            loadCourses(cb) {

                const self = this;

                let params = {
                    name: self.filters.name,
                    course_category_id: self.filters.selectedCategoryIds,
                    page: self.pagination.page,
                    per_page: self.pagination.rowsPerPage
                };

                axios.get('/admin/courses',{params: params}).then(function(response) {
                    self.items = response.data.data.data;
                    self.totalItems = response.data.data.total;
                    self.pagination.totalItems = response.data.data.total;
                    (cb || Function)();
                });
            }
        }
    }
</script>

<style scoped>
    .course_view_popup {
        min-width: 500px;
        text-align: center;
    }
</style>
