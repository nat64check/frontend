<?php

class MaxWPOptions {
	static $db = false;
	static $pages = [];
	static $page = [];
	static $prefix = '_max_wp_option_';
	static $defaults = [
		'title'    => '',
		'parent'   => false,
		'cap'      => 'manage_options',
		'network'  => false,
		'sections' => [],
	];
	static $debug = false;

	static $nonce = '_nonce_update_max_wp_options';

	static function init() {
		self::$db = $GLOBALS['wpdb'];

		if ( isset( $_GET['debug'] ) ) {
			self::$debug = true;
		}

		add_action( 'admin_menu', [ __CLASS__, 'pages' ], 99 );
		add_action( 'network_admin_menu', [ __CLASS__, 'networkPages' ], 99 );

		//add_action( 'admin_notices', array( __CLASS__, 'notices' ) );

		add_action( 'wp_ajax_maxwp_options', [ __CLASS__, 'ajax' ] );
	}

	static function prefix() {
		return self::$prefix;
	}

	static function option( $name = false, $blogId = false ) {
		$option = false;

		if ( $blogId !== false ) {
			$blogId .= '_';

			if ( $blogId == 0 || $blogId == 1 ) {
				$blogId = '';
			}

			$result = self::$db->get_var( '
                SELECT option_value
                FROM ' . self::$db->base_prefix . $blogId . 'options
                    WHERE option_name = "' . esc_sql( self::$prefix . $name ) . '"
            ' );

			if ( $result ) {
				$option = $result;
			}

			if ( is_serialized( $option ) ) {
				$option = unserialize( $option );
			}
		} else {
			$option = get_option( self::$prefix . $name );
		}

		return $option;
	}

	static function updateOption( $name = false, $val = false, $blogId = false ) {
		$result = false;

		if ( $name && $blogId !== false ) {
			switch_to_blog( $blogId );

			$result = update_option( self::$prefix . $name, $val, false );

			restore_current_blog();
		} else if ( $name ) {
			$result = update_option( self::$prefix . $name, $val, false );
		}

		return $result;
	}

	static function removeOption( $name = false ) {
		return delete_option( self::$prefix . $name );
	}

	static function pages() {
		self::setPages();

		foreach ( self::$pages as $args ) {
			if ( ! $args['network'] ) {
				if ( $args['parent'] ) {
					add_submenu_page( $args['parent'], $args['title'], $args['title'], $args['cap'], sanitize_title( $args['parent'] . $args['title'] ), [
						__CLASS__,
						'page',
					] );
				} else {
					add_menu_page( $args['title'], $args['title'], $args['cap'], sanitize_title( $args['parent'] . $args['title'] ), [
						__CLASS__,
						'page',
					] );
				}
			}
		}
	}

	static function setPages() {
		self::$pages = apply_filters( 'max_wp_option_pages', [] );

		foreach ( self::$pages as $key => $args ) {
			self::$pages[ $key ] = array_merge( self::$defaults, $args );
		}

		return self::$pages;
	}

	static function networkPages() {
		self::setPages();

		foreach ( self::$pages as $args ) {
			if ( $args['network'] ) {
				if ( $args['parent'] ) {
					add_submenu_page( $args['parent'], $args['title'], $args['title'], $args['cap'], sanitize_title( $args['parent'] . $args['title'] ), [
						__CLASS__,
						'page',
					] );
				} else {
					add_menu_page( $args['title'], $args['title'], $args['cap'], sanitize_title( $args['parent'] . $args['title'] ), [
						__CLASS__,
						'page',
					] );
				}
			}
		}
	}

	static function page() {
		self::setPage();

		wp_enqueue_media();

		if ( isset( $_POST[ self::$nonce ] ) && wp_verify_nonce( $_POST[ self::$nonce ], self::$nonce ) ) {
			self::update( $_POST, $_FILES );
		}

		self::ajaxScript();

		if ( count( self::$page['sections'] ) == 0 ) {
			echo '<p>U heeft geen secties ingesteld</p>';

			return;
		}
		?>
        <div class="wrap max-wp-settings-wrap">
			<?php self::notices(); ?>
            <form method="post" action="" enctype="multipart/form-data">
				<?php
				wp_nonce_field( self::$nonce, self::$nonce );

				if ( count( self::$page['sections'] ) > 1 ) {
					?>
                    <div class="max-wp-settings-nav">
                        <input type="submit" class="button-primary" value="Bijwerken"/>
                        <div class="max-wp-settings-tabs">
							<?php
							foreach ( self::$page['sections'] as $key => $section ) {
								?>
                                <a class="max-wp-settings-tab button"
                                   href="#max-wp-settings-tabs-section-<?php echo sanitize_title( $key ); ?>">
									<?php echo $key; ?>
                                </a>
								<?php
							}
							?>
                        </div>
                    </div>
					<?php
				} else {
					?>
                    <div class="max-wp-settings-nav">
                        <input type="submit" class="button-primary" value="Bijwerken"/>
                    </div>
					<?php
				}
				?>
                <div class="max-wp-settings-tabs-sections">
					<?php
					foreach ( self::$page['sections'] as $key => $section ) {
						echo '<div id="max-wp-settings-tabs-section-' . sanitize_title( $key ) . '" class="max-wp-settings-tabs-section" style="display: none;">';

						if ( count( self::$page['sections'] ) > 1 ) {
							echo '<h3>' . $key . '</h3>';
						}

						if ( ! empty( $section['desc'] ) ) {
							echo '<p class="description" style="margin-bottom: 20px;">' . $section['desc'] . '</p>';
						}

						if ( isset( $section['fields'] ) ) {
							$rows = [ $key => [] ];

							foreach ( $section['fields'] as $field ) {
								$field = self::getField( $field );

								if ( $field->sec ) {

									if ( ! isset( $rows[ $field->sec ] ) ) {
										$rows[ $field->sec ] = [];
									}

									$rows[ $field->sec ][] = $field;
								} else {
									$rows[ $key ][] = $field;
								}
							}

							foreach ( $rows as $row_title => $row ) {

								echo '<div class="max-wp-settings-section-row">';
								echo '<div class="max-wp-settings-section-row-title">' . $row_title . '</div>';
								echo '<div class="max-wp-settings-section-row-content">';

								echo '<table class="form-table"><body>';
								foreach ( $row as $field ) {
									self::row( $field );
								}
								echo '</tbody></table>';

								echo '</div>';
								echo '</div>';
							}
						} else if ( empty( $section['desc'] ) ) {
							echo '<p>Deze sectie heeft nog geen instellingen</p>';
						}

						echo '</div>';
					}
					?>
                </div>
            </form>
        </div>
        <script>
            jQuery(function () {
                jQuery('.max-wp-settings-tabs .max-wp-settings-tab').click(function (event) {
                    event.preventDefault();

                    window.location.hash = jQuery(this).attr('href');

                    jQuery('.max-wp-settings-tabs-section').hide();
                    jQuery(jQuery(this).attr('href')).show();
                });

                if (window.location.hash != '') {
                    jQuery(window.location.hash).show();
                } else {
                    jQuery('.max-wp-settings-tabs-section').first().show();
                }

                jQuery('.max-wp-settings-wrap input[type="checkbox"]').click(function () {
                    var el = jQuery(this);

                    jQuery('.max-wp-settings-wrap input[value="' + el.attr('value') + '"]').each(function () {
                        var input = jQuery(this);

                        if (input.attr('name') == el.attr('name')) {
                            if (input.attr('type') == 'checkbox') {
                                if (el.prop('checked')) {
                                    input.attr('checked', 'checked');
                                } else {
                                    input.removeAttr('checked');
                                }
                            }
                        }
                    });
                });
            });
        </script>
        <style>
            .max-wp-settings-nav {
                height: auto;
                overflow: hidden;
                padding: 10px 10px 0 10px;
                margin-bottom: 15px;
                border: 1px solid #eee;
                background-color: #fff;

                box-shadow: 0 1px 1px rgba(0, 0, 0, 0.04);
            }

            .max-wp-settings-nav input[type="submit"] {
                float: right;

                margin-bottom: 10px;
            }

            .max-wp-settings-nav .button {
                margin: 0 10px 10px;
            }

            .max-wp-settings-section-row {
                margin-bottom: 30px;
                background-color: #fff;
                border: 1px solid #e5e5e5;
            }

            .max-wp-settings-section-row-title {
                padding: 8px 12px;
                border-bottom: 1px solid #eee;
                font-size: 14px;
                line-height: 1.4;
                font-weight: 600;
            }

            .max-wp-settings-section-row-content {
                padding: 0 12px;
            }

            .max-wp-settings-section-row-content .form-table th,
            .max-wp-settings-section-row-content .form-table td {
                line-height: 1.5;
                padding-top: 10px;
                padding-bottom: 10px;
                font-size: 13px;
                font-weight: normal;
            }

        </style>
		<?php
	}

	static function setPage() {
		if ( isset( $_GET['page'] ) ) {
			foreach ( self::$pages as $args ) {
				if ( $_GET['page'] == sanitize_title( $args['parent'] . $args['title'] ) ) {
					self::$page = array_merge( self::$defaults, $args );

					//if( self::$debug ) {
					//    echo 'max_wp_option_page_'.sanitize_title( self::$page[ 'title' ] ).'_sections<br />';
					//}

					self::$page['sections'] = apply_filters( 'max_wp_option_page_' . sanitize_title( self::$page['title'] ) . '_sections', self::$page['sections'] );

					self::$page['sections'] = apply_filters( self::getPageFilter(), self::$page['sections'] );

					break;
				}
			}
		}
	}

	static function getPageFilter( $prefix = '' ) {
		$slug = sanitize_title( self::$page['title'] );

		if ( self::$page['parent'] ) {
			$slug .= '_' . sanitize_title( self::$page['parent'] );
		}

		$slug = str_replace( '-', '_', trim( $slug ) );
		$slug = str_replace( '_php', '', trim( $slug ) );
		$slug = 'max_wp_option_page_sections_' . $slug;

		if ( $prefix ) {
			$slug = $slug . '_' . $prefix;
		}

		if ( self::$debug ) {
			echo $slug . '<br />';
		}

		return $slug;
	}

	static function update( $data, $files ) {
		$uploadDir = (object) wp_upload_dir();

		$fields = self::getPageFields();

		$options = [];

		foreach ( $fields as $field ) {
			$name = self::name( $field->name );

			if ( isset( $data[ $name ] ) ) {
				$options[ $name ] = stripslashes_deep( $data[ $name ] );
			} else if ( isset( $files[ $name ] ) && isset( $files[ $name ]['size'] ) && $files[ $name ]['size'] > 0 ) {

				$prefix = '';

				$files[ $name ]['name'] = sanitize_file_name( $files[ $name ]['name'] );

				if ( $field->path == '' ) {
					$field->path = $uploadDir->basedir . '/max_files/';
				}

				if ( ! file_exists( $field->path ) ) {
					mkdir( $field->path );
					chmod( $field->path, 0777 );
				}

				if ( $field->value && file_exists( ABSPATH . $field->value ) ) {
					unlink( ABSPATH . $field->value );
				}

				for ( $i = 0; $i < 500; $i ++ ) {
					if ( $i > 0 ) {
						$prefix = $i . '_';
					}

					if ( ! file_exists( $field->path . $prefix . $files[ $name ]['name'] ) ) {
						$field->path .= $prefix . $files[ $name ]['name'];
						break;
					}
				}

				move_uploaded_file( $files[ $name ]['tmp_name'], $field->path );

				$options[ $name ] = str_replace( get_bloginfo( 'url' ), '', $uploadDir->baseurl . '/max_files/' . $prefix . $files[ $name ]['name'] );
			} else {
				$options[ $name ] = false;
			}
		}

		//maxwp_dump( $options );

		apply_filters( 'max_wp_option_page_save', $options, self::$page );

		foreach ( $options as $key => $val ) {
			update_option( $key, $val, false );
		}

		do_action( 'max_wp_option_page_save', $options, self::$page );

		if ( isset( $_GET['page'] ) && $_GET['page'] ) {
			if ( self::$debug ) {
				echo 'max_wp_option_page_save-' . str_replace( '-', '_', sanitize_title( esc_html( $_GET['page'] ) ) ) . '<br />';
			}

			do_action( 'max_wp_option_page_save-' . str_replace( '-', '_', sanitize_title( esc_html( $_GET['page'] ) ) ), $options, self::$page );

			do_action( self::getPageFilter( 'save' ), $options, self::$page );
		}

		flush_rewrite_rules();
	}

	static function getPageFields() {
		$fields = [];

		if ( isset( self::$page['sections'] ) && count( self::$page['sections'] ) > 0 ) {
			foreach ( self::$page['sections'] as $sec ) {
				if ( isset( $sec['fields'] ) ) {
					foreach ( $sec['fields'] as $field ) {
						$fields[] = self::getField( $field );;
					}
				}
			}
		}

		return $fields;
	}

	static function getField( $field ) {
		$field = (object) array_merge( [
			'title'   => '',
			'name'    => '',
			'type'    => 'text',
			'value'   => '',
			'values'  => [],
			'data'    => '',
			'desc'    => '',
			'checked' => '',
			'ph'      => '',
			'label'   => 'before',
			'path'    => '',
			'class'   => [],
			'size'    => 'regular',
			'objects' => [],
			'blog_id' => '',
			'default' => '',
			'limit'   => 0,
			'sec'     => '',
			'range'   => [],
			'empty'   => false,
		], $field );

		$field->name = self::$prefix . $field->name;
		$field->slug = str_replace( '[]', '', $field->name );
		$field->data = get_option( $field->slug, $field->data );

		if ( $field->type == 'checkbox' && $field->value == '' ) {
			$field->value = 'on';
		}

		if ( is_int( $field->value ) ) {
			$field->value = (string) $field->value;
		}

		if ( $field->type == 'checkbox' ) {
			if ( $field->data == $field->value ) {
				$field->checked = 'checked="checked"';
			} else if ( is_array( $field->data ) && in_array( $field->value, $field->data ) ) {
				$field->checked = 'checked="checked"';
			}
		} else if ( $field->data ) {
			$field->value = $field->data;
		}

		if ( $field->type == 'gravityforms' && class_exists( 'GFAPI' ) ) {
			$field->values[''] = 'Maak een keuze';

			if ( $forms = GFAPI::get_forms() ) {
				foreach ( $forms as $form ) {
					$field->values[ $form['id'] ] = $form['title'];
				}
			}

			$field->type = 'select';
		}

		if ( $field->type == 'select' && $field->empty ) {
			$field->values[''] = 'Maak een keuze';
		}

		if ( $field->type == 'select' && ! empty( $field->range ) ) {
			for ( $i = $field->range[0]; $i <= $field->range[1]; $i ++ ) {
				$field->values[ $i ] = $i;
			}
		}

		$field->id     = trim( str_replace( '-', '_', sanitize_title( self::$prefix . $field->name . $field->slug ) ), '_' );
		$field->row_id = sanitize_title( 'field-row-' . $field->id );

		$textTypes = [
			'color',
			'date',
			'datetime',
			'datetime-local',
			'email',
			'month',
			'number',
			'password',
			'search',
			'tel',
			'text',
			'time',
			'url',
			'week',
			'textarea',
			'list',
		];

		if ( in_array( $field->type, $textTypes ) ) {
			$field->class[] = $field->size . '-text';
		}

		$classes = '';
		foreach ( $field->class as $cl ) {
			$classes .= $cl . ' ';
		}
		$field->class = trim( $classes );

		return $field;
	}

	static function name( $name ) {
		return str_replace( [ '[', ']' ], '', $name );
	}

	static function ajaxScript() {
		?>
        <script>
            var maxwp_options = {
                working: false,
                ajax: function (args, call) {
                    var self = this;

                    var result = false;

                    var data = {
                        action: 'maxwp_options',
                        type: false
                    };

                    for (key in args) {
                        data[key] = args[key];
                    }

                    var async = false;

                    if (typeof call != 'undefined') {
                        async = true;
                    }

                    if (self.working) {
                        self.working.abort();
                    }

                    self.working = jQuery.ajax({
                        url: ajaxurl,
                        dataType: 'json',
                        type: 'GET',
                        async: async,
                        data: data,
                        success: function (response) {
                            result = response;

                            if (typeof call != 'undefined') {
                                call(result);
                            }

                            self.working = false;
                        }
                    });

                    return result;
                }
            };
        </script>
		<?php
	}

	static function notices() {
		if ( self::saved() ) {
			?>
            <div class="notice notice-success is-dismissible">
                <p>De instellingen zijn bijgewerkt</p>
            </div>
			<?php
		}
	}

	static function saved() {
		if ( isset( $_POST[ self::$nonce ] ) && wp_verify_nonce( $_POST[ self::$nonce ], self::$nonce ) ) {
			return true;
		}

		return false;
	}

	static function row( $field ) {
		$colspan = 2;

		if ( $field->title != '' ) {
			$colspan = 1;
		}

		echo '<tr>';

		$cellStyle = '';
		$label     = false;

		if ( $colspan == 1 ) {
			$label = '<label for="' . $field->id . '">' . $field->title . '</label>';
		}

		if ( $label && $field->label == 'before' ) {
			$cellStyle .= ' padding-left: 20px;';
			echo '<th width="25%" valign="top" style="padding-right: 20px;">' . $label . '</th>';
		} else if ( $label && $field->label == 'after' ) {
			$cellStyle .= ' width: 10%;';
		}

		echo '<td id="' . $field->row_id . '" valign="top" colspan="' . $colspan . '" style="' . $cellStyle . '">';
		self::field( $field );
		if ( $field->desc != '' ) {
			echo '<p style="font-style: italic; font-size: 10px;">' . $field->desc . '</p>';
		}
		echo '</td>';

		if ( $label && $field->label == 'after' ) {
			echo '<th valign="top" style="padding-left: 20px;">' . $label . '</th>';
		}

		echo '</tr>';
	}

	static function field( $field ) {
		echo '<div style="width: 100%; height: auto; overflow: hidden;">';
		$field = apply_filters( $field->name . '_before', $field );
		echo '</div>';

		$field = apply_filters( 'max_wp_options_field', $field );

		if ( $field->type == 'list' ) {
			wp_enqueue_script( 'jquery-ui-sortable' );

			if ( ! is_array( $field->objects ) ) {
				$field->objects = [ $field->objects ];
			}

			if ( ! preg_match( '|\[\]|', $field->name ) ) {
				$field->name .= '[]';
			}

			?>
            <input id="<?php echo $field->id; ?>-search" class="<?php echo $field->class; ?>" type="text"
                   name="<?php echo $field->id; ?>-search" placeholder="Type om te zoeken"/>
            <a href="#empty" id="<?php echo $field->id; ?>-empty">zoekresultaten verwijderen</a>
            <ul id="<?php echo $field->id; ?>-chosen">
				<?php
				if ( ! is_array( $field->value ) ) {
					$field->value = [];
				}

				if ( $field->blog_id ) {
					switch_to_blog( $field->blog_id );
				}

				$field->list = [];

				foreach ( $field->value as $object ) {
					$object = explode( '|', $object );

					if ( count( $object ) == 1 ) {
						$object[1] = $object[0];
						$object[0] = $field->objects[0];
					}

					$object = (object) [
						'type'  => $object[0],
						'id'    => $object[1],
						'label' => '',
					];

					if ( $object->type == 'any' ) {
						if ( $object->id && get_post( $object->id ) ) {
							$title = get_the_title( $object->id );

							if ( $label = self::getPostTypeLabel( $object->id ) ) {
								$title = $label . ' - ' . $title;
							}

							$object->label = $title;
						}
					} else if ( post_type_exists( $object->type ) ) {
						if ( $object->id && get_post( $object->id ) ) {
							$title = get_the_title( $object->id );

							if ( $label = self::getPostTypeLabel( $object->id ) ) {
								$title = $label . ' - ' . $title;
							}

							$object->label = $title;
						}
					} else if ( taxonomy_exists( $object->type ) ) {
						if ( $term = get_term_by( 'id', $object->id, $object->type ) ) {
							$title = $term->name;

							if ( $tax = get_taxonomy( $object->type ) ) {
								$title = $tax->labels->name . ' - ' . $title;
							}

							$object->label = $title;
						}

					}

					//maxwp_dump( $object, false );

					if ( $object->label ) {
						$field->list[] = $object;
					}
				}

				//maxwp_dump( $field->list, false );

				$field = apply_filters( 'max_wp_options_field_list', $field );

				//maxwp_dump( $field->list, false );

				if ( $field->blog_id ) {
					restore_current_blog();
				}

				foreach ( $field->list as $object ) {
					?>
                    <li class="<?php echo $field->id; ?>-chosen">
                        <input type="hidden" name="<?php echo $field->name; ?>"
                               value="<?php echo $object->type . '|' . $object->id; ?>"/>
						<?php echo $object->label; ?>
                        <a href="#remove">x</a>
                    </li>
					<?php
				}
				?>
            </ul>
            <div id="<?php echo $field->id; ?>-results"></div>
            <script>
                var <?php echo $field->id; ?>list = {
                    id: '<?php echo $field->id; ?>',
                    default: '<?php echo $field->default; ?>',
                    limit: <?php echo $field->limit; ?>,
                    init: function () {
                        var self = this;

                        jQuery('#' + self.id + '-search').keyup(function (event) {
                            self.search(event, jQuery(this));
                        });
                        jQuery('#' + self.id + '-search').click(function (event) {
                            self.search(event, jQuery(this));
                        });

                        if (self.default == 'show') {
                            jQuery('#' + self.id + '-search').click();
                        }

                        jQuery('#' + self.id + '-empty').click(function (event) {
                            self.empty(event, jQuery(this));
                        });

                        jQuery('.' + self.id + '-chosen a').click(function (event) {
                            self.remove(event, jQuery(this));
                        });

                        jQuery('#' + self.id + '-chosen').sortable();
                    },
                    search: function (event, el) {
                        var self = this;

                        var args = {
                            type: 'object_search',
                            objects: <?php echo json_encode( $field->objects ); ?>,
                            blog_id: '<?php echo $field->blog_id; ?>',
                            s: el.val(),
                            chosen: []
                        };

                        if (jQuery('#' + self.id + '-chosen li input').length > 0) {
                            jQuery('#' + self.id + '-chosen li input').each(function () {
                                args.chosen.push(jQuery(this).attr('value'));
                            });
                        }

                        maxwp_options.ajax(args, self.results);
                    },
                    results: function (result) {
                        var self = <?php echo $field->id; ?>list;

                        var html = '';

                        if (typeof result.data != 'undefined') {
                            for (type in result.data) {
                                var type_data = result.data[type];

                                for (key in type_data) {
                                    html += '<a href="' + key + '" data-type="' + type + '">' + type_data[key] + ' +</a> ';
                                }
                            }
                        } else {
                            html += 'Geen resultaten gevonden';
                        }

                        jQuery('#' + self.id + '-results').html(html).addClass('active');

                        jQuery('#' + self.id + '-results a').unbind().click(function (event) {
                            self.add(event, jQuery(this));
                        });

                        jQuery('#' + self.id + '-empty').show();
                    },
                    add: function (event, el) {
                        var self = this;

                        event.preventDefault();

                        if (self.limit == 1 && jQuery('.' + self.id + '-chosen').length == 1) {
                            jQuery('#' + self.id + '-chosen').empty();
                        }

                        if (jQuery('#' + self.id + '-chosen a input[value="' + el.attr('href') + '"]').length == 0) {
                            jQuery('#' + self.id + '-chosen').append(
                                '<li class="' + self.id + '-chosen">' +
                                '<input type="hidden" name="<?php echo $field->name; ?>" value="' + el.data('type') + '|' + el.attr('href') + '" />' +
                                el.text().replace(' +', '') +
                                '<a href="#remove">x</a>' +
                                '</li>'
                            );

                            jQuery('.' + self.id + '-chosen a').unbind().click(function (event) {
                                self.remove(event, jQuery(this));
                            });
                        }

                        el.remove();
                    },
                    remove: function (event, el) {
                        event.preventDefault();

                        el.parent().remove();
                    },
                    empty: function (event, el) {
                        var self = this;

                        event.preventDefault();

                        jQuery('#' + self.id + '-results').empty().removeClass('active');

                        el.hide();
                    }
                };

                jQuery(function () {
					<?php echo $field->id; ?>list.init();
                });
            </script>
            <style>
                #<?php echo $field->id; ?>-empty {
                    display: none;
                }

                #<?php echo $field->id; ?>-chosen {
                    width: 100%;
                    height: auto;

                    overflow: hidden;
                }

                #<?php echo $field->id; ?>-chosen li {
                    position: relative;
                    float: left;
                    display: block;
                    padding: 5px 35px 5px 10px;
                    background-color: #0085ba;
                    white-space: nowrap;
                    margin: 10px 10px 0px 0px;
                    color: #fff;
                    text-decoration: none;
                    cursor: move;
                }

                #<?php echo $field->id; ?>-chosen li a {
                    position: absolute;
                    top: 0px;
                    right: 0px;
                    padding: 5px 10px;
                    background-color: #C20032;
                    color: #fff;
                    text-decoration: none;

                    display: block;
                }

                #<?php echo $field->id; ?>-chosen li a:hover {
                    background-color: #999;
                    color: #fff;
                }

                #<?php echo $field->id; ?>-results {
                    width: 100%;
                    height: auto;
                    margin-top: 24px;
                    padding-top: 14px;
                    border-top: 2px solid #999;
                    overflow: hidden;

                    display: none;
                }

                #<?php echo $field->id; ?>-results.active {
                    display: block;
                }

                #<?php echo $field->id; ?>-results a {
                    float: left;
                    display: block;
                    padding: 5px 10px;
                    background-color: #999;
                    white-space: nowrap;
                    margin: 10px 10px 0px 0px;
                    color: #fff;
                    text-decoration: none;
                }

                #<?php echo $field->id; ?>-results a:hover {
                    background-color: #0085ba;
                    color: #fff;
                }
            </style>
			<?php
		} else if ( $field->type == 'wysiwyg' ) {
			$field = apply_filters( 'max_wp_options_field_wysiwyg', $field );

			wp_editor( stripslashes( $field->value ), $field->id, [
				'textarea_name' => $field->name,
				'media_buttons' => true,
			] );
		} else if ( $field->type == 'image' || $field->type == 'img' ) {
			$img = '';

			if ( $field->value ) {

				if ( is_array( $field->value ) && isset( $field->value[0] ) ) {
					$field->value = $field->value[0];
				}

				//maxwp_dump( $field, false );

				$img = wp_get_attachment_url( $field->value );

				if ( $imgProps = wp_get_attachment_image_src( $field->value, 'thumbnail' ) ) {
					$img = $imgProps[0];
				}
			}

			$field = apply_filters( 'max_wp_options_field_img', $field );

			?>
            <div class="img-select">
                <div class="img-preview">
                    <img src="<?php echo $img; ?>" alt="Geen afbeelding gekozen"/>
                </div>
                <div class="img-actions">
                    <input type="button" class="button img-button-choose" value="Kiezen"/>
                    <input type="button" class="button img-button-remove" value="Verwijderen"/>
                </div>
            </div>
            <input type="hidden" name="<?php echo $field->name; ?>" id="<?php echo $field->id; ?>"
                   value="<?php echo $field->value; ?>">
            <script>
                jQuery(function () {
                    jQuery('#<?php echo $field->row_id; ?> .img-button-choose').click(function (event) {
                        event.preventDefault();

                        var media = wp.media({
                            title: 'Selecteer een afbeelding',
                            multiple: false,
                            library: {
                                type: 'image'
                            },
                            button: {
                                text: 'Selecteren'
                            }
                        });

                        media.open();

                        media.on('select', function () {
                            media.state().get('selection').each(function (attachment) {
                                jQuery('#<?php echo $field->id; ?>').val(attachment.id);

                                var imgSrc = attachment.attributes.url;

                                if (
                                    typeof attachment.attributes.sizes != 'undefined' &&
                                    typeof attachment.attributes.sizes.thumbnail != 'undefined' &&
                                    typeof attachment.attributes.sizes.thumbnail.url != 'undefined'
                                ) {
                                    imgSrc = attachment.attributes.sizes.thumbnail.url;
                                }

                                jQuery('#<?php echo $field->row_id; ?> .img-preview img').attr('src', imgSrc);
                            });
                        });
                    });

                    jQuery('#<?php echo $field->row_id; ?> .img-button-remove').click(function (event) {
                        event.preventDefault();

                        jQuery('#<?php echo $field->id; ?>').val('');
                        jQuery('#<?php echo $field->row_id; ?> .img-preview img').attr('src', '');
                    });
                });
            </script>
            <style>
                #
                <?php echo $field->row_id; ?>
                .img-select {
                    width: 100%;
                    height: auto;
                    overflow: hidden;
                }

                #
                <?php echo $field->row_id; ?>
                .img-select .img-preview {
                    float: left;
                    width: 170px;
                    margin-right: 15px;
                }

                #
                <?php echo $field->row_id; ?>
                .img-select .img-preview img {
                    width: auto;
                    max-width: 150px;
                    height: auto;
                    max-height: 150px;
                    background-color: #efefef;
                    padding: 5px;
                }

                #
                <?php echo $field->row_id; ?>
                .img-select .img-actions {
                    float: left;
                }
            </style>
			<?php
		} else if ( $field->type == 'select' ) {

			$field = apply_filters( 'max_wp_options_field_select', $field );

			echo '<select id="' . $field->id . '" name="' . $field->name . '">';

			foreach ( $field->values as $key => $val ) {
				$selected = '';

				if ( $key == $field->data || ( $field->data == '' && $field->value == $key ) ) {
					$selected = 'selected="selected"';
				}

				echo '<option value="' . $key . '" ' . $selected . '>' . $val . '</option>';
			}

			echo '</select>';
		} else if ( $field->type == 'customfile' ) {

			$field = apply_filters( 'max_wp_options_field_customfile', $field );

			if ( $field->value ) {
				echo '<a href="' . $field->value . '">' . basename( $field->value ) . '</a><br />';
			}

			echo '<input id="' . $field->id . '" type="file" name="' . $field->name . '" value="' . $field->value . '" />';
		} else if ( $field->type == 'file' ) {

			$field = apply_filters( 'max_wp_options_field_file', $field );

			echo '<input id="' . $field->id . '" type="hidden" name="' . $field->name . '" value="' . $field->value . '" />';

			?>
            <div id="<?php echo $field->id; ?>-label" style="float: left; margin-right: 10px; line-height: 26px;">
				<?php
				if ( $field->value ) {
					echo '<a href="' . wp_get_attachment_url( $field->value ) . '">' . get_the_title( $field->value ) . '</a>';
				}
				?>
            </div>
            <input type="button" class="button file-button-choose" value="Kiezen"
			       <?php if ( $field->value ) { ?>style="display: none;"<?php } ?> />
            <input type="button" class="button file-button-remove" value="Verwijderen"/>
            <script>
                jQuery(function () {
                    jQuery('#<?php echo $field->row_id; ?> .file-button-choose').click(function (event) {
                        event.preventDefault();

                        var self = this;

                        var media = wp.media({
                            title: 'Selecteer een bestand',
                            multiple: false,
                            button: {
                                text: 'Selecteren'
                            }
                        });

                        media.open();

                        media.on('select', function () {
                            media.state().get('selection').each(function (attachment) {
                                jQuery('#<?php echo $field->id; ?>').val(attachment.id);

                                jQuery('#<?php echo $field->id; ?>-label').html(
                                    '<a href="' + attachment.attributes.url + '" target="_blank">' +
                                    attachment.attributes.name +
                                    '</a>'
                                );

                                jQuery(self).css('display', 'none');
                            });
                        });
                    });

                    jQuery('#<?php echo $field->row_id; ?> .file-button-remove').click(function (event) {
                        event.preventDefault();

                        jQuery('#<?php echo $field->id; ?>').val('');

                        jQuery('#<?php echo $field->id; ?>-label').empty();

                        jQuery(this).parent().find('.file-button-choose').css('display', 'inline-block');
                    });

                });
            </script>
			<?php
		} else if ( $field->type == 'textarea' ) {

			$field = apply_filters( 'max_wp_options_field_textarea', $field );

			echo '<textarea id="' . $field->id . '" class="' . $field->class . '" name="' . $field->name . '" placeholder="' . $field->ph . '" ' . $field->checked . ' style="width: 100%; min-height: 150px; resize: vertical;">' . $field->value . '</textarea>';
		} else {
			$type = $field->type;

			if ( $field->type == 'color' ) {
				$type = 'text';
			}

			$field = apply_filters( 'max_wp_options_field_input', $field );

			echo '<input id="' . $field->id . '" class="' . $field->class . '" type="' . $type . '" name="' . $field->name . '" placeholder="' . $field->ph . '" value="' . $field->value . '" ' . $field->checked . ' />';
		}

		if ( $field->type == 'color' ) {
			wp_enqueue_script( 'iris' );
			?>
            <script>
                jQuery(function () {
                    jQuery('#<?php echo $field->id; ?>').iris({
                        defaultColor: true,
                        hide: true,
                        palettes: true
                    });
                });
            </script>
			<?php
		}

		echo '<div style="width: 100%; height: auto; overflow: hidden;">';
		$field = apply_filters( $field->name . '_after', $field );
		echo '</div>';
	}

	static function getPostTypeLabel( $p = false ) {
		$label = '';

		if ( ! isset( $p->post_type ) ) {
			$p = get_post( $p );
		}

		if ( $p && isset( $p->post_type ) ) {
			$type = get_post_type_object( $p->post_type );

			if ( isset( $type->labels->singular_name ) ) {
				$label = $type->labels->singular_name;
			}
		}

		return $label;
	}

	static function ajax() {
		$result = (object) [
			'error' => false,
			'data'  => [],
		];

		$args = (object) $_GET;

		foreach ( $args as $key => $arg ) {
			if ( is_array( $arg ) ) {
				foreach ( $arg as $k => $v ) {
					$arg[ $k ] = esc_html( $v );
				}
				$args->$key = $arg;
			} else {
				$args->$key = esc_html( $arg );
			}
		}

		if ( $args->type == 'object_search' ) {

			if ( $args->blog_id ) {
				switch_to_blog( $args->blog_id );
			}

			if ( ! isset( $args->chosen ) ) {
				$args->chosen = [];
			}

			if ( ! is_array( $args->objects ) ) {
				$args->objects = [ $args->objects ];
			}

			$limit = 10;

			foreach ( $args->objects as $object ) {

				if ( ! isset( $result->data[ $object ] ) ) {
					$result->data[ $object ] = [];
				}

				if ( taxonomy_exists( $object ) ) {

					$data = get_terms( [
						'taxonomy' => $object,
						'number'   => $limit,
						'search'   => $args->s,
						'exclude'  => $args->chosen,
					] );

					//maxwp_dump( $data );

					foreach ( $data as $t ) {

						if ( $tax = get_taxonomy( $t->taxonomy ) ) {
							$t->name = $tax->labels->name . ' - ' . $t->name;
						}

						$result->data[ $object ][ $t->term_id ] = $t->name;
					}
				} else if ( $object == 'product_attributes' ) {
					$query = '
                        SELECT DISTINCT attribute_id, attribute_label
                        FROM ' . self::$db->prefix . 'woocommerce_attribute_taxonomies 
                        WHERE attribute_label LIKE "%' . esc_sql( $args->s ) . '%"
                        LIMIT ' . $limit . '
                    ';

					if ( $results = self::$db->get_results( $query ) ) {
						foreach ( $results as $attr ) {
							$result->data[ $object ][ $attr->attribute_id ] = $attr->attribute_label;
						}
					}
				} else {
					$data = get_posts( [
						'post_type'      => $object,
						'posts_per_page' => $limit,
						's'              => $args->s,
						'post__not_in'   => $args->chosen,
					] );

					//maxwp_dump( $args );

					foreach ( $data as $p ) {
						if ( $label = self::getPostTypeLabel( $p ) ) {
							$p->post_title = $label . ' - ' . $p->post_title;
						}

						$result->data[ $object ][ $p->ID ] = $p->post_title;
					}
				}
			}

			if ( $args->blog_id ) {
				restore_current_blog();
			}
		}

		$result = apply_filters( 'max_wp_options_ajax_result', $result, $args );

		wp_send_json( $result );
		exit;
	}
}

MaxWPOptions::init();
