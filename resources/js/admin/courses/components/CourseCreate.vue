<template>
    <div class="component-wrap">
        <!-- form -->
        <v-card>
            <v-form ref="courseFormUpload" lazy-validation>
                <v-container grid-list-md>
                    <v-layout row wrap>
                        <v-flex xs12 sm8>
                            <v-select
                                label="Upload To Course Category"
                                v-bind:items="courseCategories"
                                v-model="uploadTo"
                                item-text="name"
                                item-value="id"
                            ></v-select>
                        </v-flex>
                        <v-flex xs12 sm4>
                            <v-btn @click="clear()" block class="primary lighten-1" dark>
                                Clear
                            </v-btn>
                        </v-flex>
                        <v-flex xs12>
                            <div class="dropzone" id="courseupload"></div>
                        </v-flex>
                    </v-layout>
                </v-container>
            </v-form>
        </v-card>
        <!-- /form -->
    </div>
</template>

<script>
    export default {
        data() {
            return {
                dropzone: null,
                courseCategories: [],
                uploadTo: '',
                addedCourses: []
            }
        },
        mounted() {
            console.log('pages.courses.components.CourseUpload.vue');

            const self = this;

            self.loadCourseCategories(()=>{});
            self.initDropzone();

            self.$eventBus.$on(['FILE_GROUP_ADDED'],()=>{
                self.loadCourseCategories(()=>{});
            });
        },
        methods: {
            clear() {
                const self = this;

                _.each(self.addedCourses,f=>{
                    self.dropzone.removeCourse(f);
                });

                self.addedCourses = [];
            },
            upload() {

                const self = this;

                self.dropzone.processQueue();
            },
            initDropzone() {

                const self = this;

                Dropzone.autoDiscover = false;

                self.dropzone = new Dropzone("#courseupload", {
                    url:'/admin/courses',
                    paramName: "course", // The name that will be used to transfer the course
                    maxCoursesize: 50, // 50MB
                    uploadMultiple: true,
                    //acceptedCourses: 'image/*',
                    headers: {'X-CSRF-TOKEN' : _token},
                    autoProcessQueue: true,
                    init: function() {
                        // initial hook
                    },
                    success: function(course, response){
                        // success hook
                    }
                });

                self.dropzone.on("addedcourse", function(course) {
                    if(!self.uploadTo) {
                        self.$store.commit('showSnackbar',{
                            message: "Please choose course category to upload the course(s)",
                            color: 'error',
                            duration: 3000
                        });
                        self.dropzone.removeCourse(course);
                    } else {
                        self.addedCourses.push(course);
                    }
                });

                self.dropzone.on('sending',(course,xhr,formData)=> {
                    formData.append('course_category_id',self.uploadTo);
                });

                self.dropzone.on("complete", function(course) {
                    self.$store.commit('showSnackbar',{
                        message: "Course(s) uploaded successfully.",
                        color: 'success',
                        duration: 3000
                    });

                    self.$eventBus.$emit('FILE_UPLOADED');
                });
            },
            loadCourseCategories(cb) {

                const self = this;

                let params = {
                    paginate: 'no'
                };

                axios.get('/admin/course-categories',{params: params}).then(function(response) {
                    self.courseCategories = response.data.data;
                    cb();
                });
            }
        }
    }
</script>
<style scoped>
    #courseupload {
        min-height: 400px;
        background: grey;
        border: 1px dashed #eaeaea;
    }
    .dropzone .dz-preview.dz-image-preview {
        background: none;
    }
</style>
