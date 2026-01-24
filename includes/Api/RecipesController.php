<?php
/**
 * Recipes API Controller
 *
 * REST API endpoints for recipe library
 *
 * @package PracticeRx
 */

namespace PracticeRx\Api;

use PracticeRx\Models\Recipe;
use PracticeRx\Core\Helper;
use WP_Error;
use WP_REST_Request;
use WP_REST_Response;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class RecipesController extends ApiController {
	
	protected $resource_name = 'recipes';
	
	/**
	 * Register routes
	 */
	public function register_routes() {
		register_rest_route( $this->namespace, '/' . $this->resource_name, array(
			array(
				'methods'             => 'GET',
				'callback'            => array( $this, 'get_items' ),
				'permission_callback' => array( $this, 'check_permissions' ),
			),
			array(
				'methods'             => 'POST',
				'callback'            => array( $this, 'create_item' ),
				'permission_callback' => array( $this, 'check_permissions' ),
			),
		) );
		
		register_rest_route( $this->namespace, '/' . $this->resource_name . '/(?P<id>[\d]+)', array(
			array(
				'methods'             => 'GET',
				'callback'            => array( $this, 'get_item' ),
				'permission_callback' => array( $this, 'check_permissions' ),
			),
			array(
				'methods'             => 'PUT',
				'callback'            => array( $this, 'update_item' ),
				'permission_callback' => array( $this, 'check_permissions' ),
			),
			array(
				'methods'             => 'DELETE',
				'callback'            => array( $this, 'delete_item' ),
				'permission_callback' => array( $this, 'check_permissions' ),
			),
		) );
		
		register_rest_route( $this->namespace, '/' . $this->resource_name . '/search', array(
			'methods'             => 'GET',
			'callback'            => array( $this, 'search_recipes' ),
			'permission_callback' => array( $this, 'check_permissions' ),
		) );
		
		register_rest_route( $this->namespace, '/' . $this->resource_name . '/public', array(
			'methods'             => 'GET',
			'callback'            => array( $this, 'get_public' ),
			'permission_callback' => '__return_true',
		) );
	}
	
	/**
	 * Get all recipes
	 *
	 * @param WP_REST_Request $request Request object
	 * @return WP_REST_Response|WP_Error
	 */
	public function get_items( $request ) {
		$practitioner_id = $request->get_param( 'practitioner_id' );
		$meal_type = $request->get_param( 'meal_type' );
		$public_only = $request->get_param( 'public_only' ) === 'true';
		
		if ( $public_only ) {
			$recipes = Recipe::get_public();
		} elseif ( $practitioner_id ) {
			$recipes = Recipe::get_by_practitioner( $practitioner_id );
		} elseif ( $meal_type ) {
			$recipes = Recipe::get_by_meal_type( $meal_type );
		} else {
			$recipes = Recipe::get_all();
		}
		
		// Decode JSON fields
		foreach ( $recipes as &$recipe ) {
			$recipe->ingredients = json_decode( $recipe->ingredients, true );
			$recipe->instructions = json_decode( $recipe->instructions, true );
		}
		
		return Helper::format_response( $recipes );
	}
	
	/**
	 * Get single recipe
	 *
	 * @param WP_REST_Request $request Request object
	 * @return WP_REST_Response|WP_Error
	 */
	public function get_item( $request ) {
		$recipe = Recipe::get( $request['id'] );
		
		if ( ! $recipe ) {
			return new WP_Error( 'recipe_not_found', __( 'Recipe not found', 'practicerx' ), array( 'status' => 404 ) );
		}
		
		// Decode JSON fields
		$recipe->ingredients = json_decode( $recipe->ingredients, true );
		$recipe->instructions = json_decode( $recipe->instructions, true );
		
		return Helper::format_response( $recipe );
	}
	
	/**
	 * Create recipe
	 *
	 * @param WP_REST_Request $request Request object
	 * @return WP_REST_Response|WP_Error
	 */
	public function create_item( $request ) {
		$ingredients = $request->get_param( 'ingredients' );
		$instructions = $request->get_param( 'instructions' );
		
		$data = array(
			'practitioner_id' => $request->get_param( 'practitioner_id' ),
			'title'           => sanitize_text_field( $request->get_param( 'title' ) ),
			'description'     => wp_kses_post( $request->get_param( 'description' ) ),
			'image_url'       => esc_url_raw( $request->get_param( 'image_url' ) ?: '' ),
			'meal_type'       => sanitize_text_field( $request->get_param( 'meal_type' ) ?: 'lunch' ),
			'prep_time'       => intval( $request->get_param( 'prep_time' ) ?: 0 ),
			'cook_time'       => intval( $request->get_param( 'cook_time' ) ?: 0 ),
			'servings'        => intval( $request->get_param( 'servings' ) ?: 1 ),
			'calories'        => intval( $request->get_param( 'calories' ) ?: 0 ),
			'protein'         => floatval( $request->get_param( 'protein' ) ?: 0 ),
			'carbs'           => floatval( $request->get_param( 'carbs' ) ?: 0 ),
			'fats'            => floatval( $request->get_param( 'fats' ) ?: 0 ),
			'ingredients'     => is_array( $ingredients ) ? wp_json_encode( $ingredients ) : $ingredients,
			'instructions'    => is_array( $instructions ) ? wp_json_encode( $instructions ) : $instructions,
			'tags'            => sanitize_text_field( $request->get_param( 'tags' ) ?: '' ),
			'is_public'       => $request->get_param( 'is_public' ) === true ? 1 : 0,
		);
		
		$id = Recipe::create( $data );
		
		if ( ! $id ) {
			return new WP_Error( 'creation_failed', __( 'Failed to create recipe', 'practicerx' ), array( 'status' => 500 ) );
		}
		
		$recipe = Recipe::get( $id );
		$recipe->ingredients = json_decode( $recipe->ingredients, true );
		$recipe->instructions = json_decode( $recipe->instructions, true );
		
		return Helper::format_response( $recipe, 201 );
	}
	
	/**
	 * Update recipe
	 *
	 * @param WP_REST_Request $request Request object
	 * @return WP_REST_Response|WP_Error
	 */
	public function update_item( $request ) {
		$recipe = Recipe::get( $request['id'] );
		
		if ( ! $recipe ) {
			return new WP_Error( 'recipe_not_found', __( 'Recipe not found', 'practicerx' ), array( 'status' => 404 ) );
		}
		
		$data = array();
		$fields = array( 'title', 'description', 'image_url', 'meal_type', 'prep_time', 
			'cook_time', 'servings', 'calories', 'protein', 'carbs', 'fats', 'tags', 'is_public' );
		
		foreach ( $fields as $field ) {
			if ( $request->has_param( $field ) ) {
				$data[ $field ] = $request->get_param( $field );
			}
		}
		
		if ( $request->has_param( 'ingredients' ) ) {
			$ingredients = $request->get_param( 'ingredients' );
			$data['ingredients'] = is_array( $ingredients ) ? wp_json_encode( $ingredients ) : $ingredients;
		}
		if ( $request->has_param( 'instructions' ) ) {
			$instructions = $request->get_param( 'instructions' );
			$data['instructions'] = is_array( $instructions ) ? wp_json_encode( $instructions ) : $instructions;
		}
		
		$updated = Recipe::update( $request['id'], $data );
		
		if ( ! $updated ) {
			return new WP_Error( 'update_failed', __( 'Failed to update recipe', 'practicerx' ), array( 'status' => 500 ) );
		}
		
		$recipe = Recipe::get( $request['id'] );
		$recipe->ingredients = json_decode( $recipe->ingredients, true );
		$recipe->instructions = json_decode( $recipe->instructions, true );
		
		return Helper::format_response( $recipe );
	}
	
	/**
	 * Delete recipe
	 *
	 * @param WP_REST_Request $request Request object
	 * @return WP_REST_Response|WP_Error
	 */
	public function delete_item( $request ) {
		$recipe = Recipe::get( $request['id'] );
		
		if ( ! $recipe ) {
			return new WP_Error( 'recipe_not_found', __( 'Recipe not found', 'practicerx' ), array( 'status' => 404 ) );
		}
		
		$deleted = Recipe::delete( $request['id'] );
		
		if ( ! $deleted ) {
			return new WP_Error( 'deletion_failed', __( 'Failed to delete recipe', 'practicerx' ), array( 'status' => 500 ) );
		}
		
		return Helper::format_response( array( 'deleted' => true ) );
	}
	
	/**
	 * Search recipes
	 *
	 * @param WP_REST_Request $request Request object
	 * @return WP_REST_Response|WP_Error
	 */
	public function search_recipes( $request ) {
		$term = $request->get_param( 'q' );
		$practitioner_id = $request->get_param( 'practitioner_id' ) ?: 0;
		
		if ( empty( $term ) ) {
			return new WP_Error( 'no_search_term', __( 'Search term required', 'practicerx' ), array( 'status' => 400 ) );
		}
		
		$recipes = Recipe::search( $term, $practitioner_id );
		
		foreach ( $recipes as &$recipe ) {
			$recipe->ingredients = json_decode( $recipe->ingredients, true );
			$recipe->instructions = json_decode( $recipe->instructions, true );
		}
		
		return Helper::format_response( $recipes );
	}
	
	/**
	 * Get public recipes
	 *
	 * @param WP_REST_Request $request Request object
	 * @return WP_REST_Response|WP_Error
	 */
	public function get_public( $request ) {
		$recipes = Recipe::get_public();
		
		foreach ( $recipes as &$recipe ) {
			$recipe->ingredients = json_decode( $recipe->ingredients, true );
			$recipe->instructions = json_decode( $recipe->instructions, true );
		}
		
		return Helper::format_response( $recipes );
	}
}
