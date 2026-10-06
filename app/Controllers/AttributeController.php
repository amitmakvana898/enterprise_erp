<?php

namespace App\Controllers;

use App\Core\Controller;
use App\Core\Request;
use App\Core\Response;
use App\Core\Session;
use App\Core\Database;
use App\Helpers\Validator;
use App\Services\AuditService;

class AttributeController extends Controller {
    public function index(): void {
        if (!has_permission('products.attributes')) {
            Session::setFlash('error', 'Access Denied: You do not have permission (products.attributes) to view the Dynamic Attribute Engine!', 'danger');
            (new Response())->redirect(url('/dashboard'));
            return;
        }

        $db = Database::getInstance();
        $attributes = $db->query("
            SELECT a.*, g.name AS group_name,
                   (SELECT COUNT(DISTINCT pa.product_id) FROM product_attributes pa WHERE pa.attribute_id = a.id) AS usage_count
            FROM attributes a
            LEFT JOIN attribute_groups g ON a.group_id = g.id
            ORDER BY a.id DESC
        ")->fetchAll();
        
        $groups = $db->query("
            SELECT g.*, (SELECT COUNT(*) FROM attributes a WHERE a.group_id = g.id) AS attr_count 
            FROM attribute_groups g 
            ORDER BY g.name ASC
        ")->fetchAll();

        $this->render('attributes/index', [
            'title' => 'Dynamic Attribute Engine (EAV Master)', 
            'attributes' => $attributes, 
            'groups' => $groups
        ]);
    }

    public function store(): void {
        $request = new Request();
        $response = new Response();
        $validator = new Validator();
        $data = $request->getBody();

        if (!$validator->validate($data, ['name' => 'required', 'code' => 'required', 'type' => 'required'])) {
            Session::setFlash('error', $validator->getFirstError(), 'danger');
            $response->redirect(url('/attributes'));
        }

        $code = strtolower(preg_replace('/[^a-z0-9_]/', '_', trim($data['code'])));
        $optionsArray = [];
        if (!empty($data['options']) && is_array($data['options'])) {
            foreach ($data['options'] as $opt) {
                $trimmed = trim($opt);
                if ($trimmed !== '') {
                    $optionsArray[] = $trimmed;
                }
            }
        } elseif (!empty($data['options_json'])) {
            $rawOptions = explode(',', $data['options_json']);
            foreach ($rawOptions as $opt) {
                $trimmed = trim($opt);
                if ($trimmed !== '') {
                    $optionsArray[] = $trimmed;
                }
            }
        }

        $isRequired = isset($data['is_required']) ? (int)$data['is_required'] : 1;
        $defaultValue = !empty($data['default_value']) ? trim($data['default_value']) : null;
        if (empty($defaultValue) && !empty($optionsArray)) {
            $defaultValue = $optionsArray[0];
        }

        $db = Database::getInstance();
        try {
            $stmt = $db->prepare("INSERT INTO attributes (group_id, name, code, type, options_json, is_required, default_value) VALUES (:group_id, :name, :code, :type, :options, :req, :def)");
            $stmt->execute([
                'group_id' => !empty($data['group_id']) ? (int)$data['group_id'] : null,
                'name' => trim($data['name']),
                'code' => $code,
                'type' => $data['type'],
                'options' => !empty($optionsArray) ? json_encode($optionsArray) : null,
                'req' => $isRequired,
                'def' => $defaultValue
            ]);

            $attrId = (int)$db->lastInsertId();
            AuditService::log('AttributeEngine', 'CREATE_ATTRIBUTE', $attrId, null, $data);
            Session::setFlash('success', "Dynamic Attribute '{$data['name']}' registered successfully!", 'success');
        } catch (\Exception $e) {
            Session::setFlash('error', 'Failed to register attribute: ' . $e->getMessage(), 'danger');
        }

        $response->redirect(url('/attributes'));
    }

    public function update(int $id): void {
        $request = new Request();
        $response = new Response();
        $data = $request->getBody();

        if (empty($data['name'])) {
            Session::setFlash('error', 'Attribute name is required.', 'danger');
            $response->redirect(url('/attributes'));
            return;
        }

        $optionsArray = [];
        if (!empty($data['options_json'])) {
            $rawOptions = explode(',', $data['options_json']);
            foreach ($rawOptions as $opt) {
                $trimmed = trim($opt);
                if ($trimmed !== '') {
                    $optionsArray[] = $trimmed;
                }
            }
        }

        $isRequired = isset($data['is_required']) ? (int)$data['is_required'] : 0;
        $defaultValue = !empty($data['default_value']) ? trim($data['default_value']) : null;
        if (empty($defaultValue) && !empty($optionsArray)) {
            $defaultValue = $optionsArray[0];
        }

        $db = Database::getInstance();
        try {
            $stmt = $db->prepare("
                UPDATE attributes 
                SET group_id = :group_id, name = :name, type = :type, options_json = :options, is_required = :req, default_value = :def 
                WHERE id = :id
            ");
            $stmt->execute([
                'group_id' => !empty($data['group_id']) ? (int)$data['group_id'] : null,
                'name' => trim($data['name']),
                'type' => $data['type'] ?? 'text',
                'options' => !empty($optionsArray) ? json_encode($optionsArray) : null,
                'req' => $isRequired,
                'def' => $defaultValue,
                'id' => $id
            ]);

            AuditService::log('AttributeEngine', 'UPDATE_ATTRIBUTE', $id, null, $data);
            Session::setFlash('success', "Dynamic Attribute '{$data['name']}' updated successfully!", 'success');
        } catch (\Exception $e) {
            Session::setFlash('error', 'Failed to update attribute: ' . $e->getMessage(), 'danger');
        }

        $response->redirect(url('/attributes'));
    }

    public function storeGroup(): void {
        $request = new Request();
        $response = new Response();
        $data = $request->getBody();

        if (empty($data['name'])) {
            Session::setFlash('error', 'Attribute Group name is required.', 'danger');
            $response->redirect(url('/attributes'));
        }

        $name = trim($data['name']);
        $db = Database::getInstance();
        try {
            $stmt = $db->prepare("INSERT INTO attribute_groups (name) VALUES (:name) ON DUPLICATE KEY UPDATE name = VALUES(name)");
            $stmt->execute(['name' => $name]);
            
            Session::setFlash('success', "Attribute Group '{$name}' created successfully!", 'success');
        } catch (\Exception $e) {
            Session::setFlash('error', 'Failed to add attribute group: ' . $e->getMessage(), 'danger');
        }

        $response->redirect(url('/attributes'));
    }

    public function deleteGroup(int $id): void {
        $response = new Response();
        $db = Database::getInstance();

        try {
            $db->exec("SET FOREIGN_KEY_CHECKS = 0");
            $db->prepare("UPDATE attributes SET group_id = NULL WHERE group_id = :id")->execute(['id' => $id]);
            $db->prepare("DELETE FROM attribute_groups WHERE id = :id")->execute(['id' => $id]);
            $db->exec("SET FOREIGN_KEY_CHECKS = 1");

            Session::setFlash('success', 'Attribute Group family deleted successfully.', 'success');
        } catch (\Exception $e) {
            $db->exec("SET FOREIGN_KEY_CHECKS = 1");
            Session::setFlash('error', 'Failed to delete attribute group: ' . $e->getMessage(), 'danger');
        }

        $response->redirect(url('/attributes'));
    }

    public function delete(int $id): void {
        $response = new Response();
        $db = Database::getInstance();

        try {
            $db->exec("SET FOREIGN_KEY_CHECKS = 0");
            $db->prepare("DELETE FROM product_attributes WHERE attribute_id = :id")->execute(['id' => $id]);
            $db->prepare("DELETE FROM attributes WHERE id = :id")->execute(['id' => $id]);
            $db->exec("SET FOREIGN_KEY_CHECKS = 1");

            Session::setFlash('success', 'Dynamic Attribute deleted successfully.', 'success');
        } catch (\Exception $e) {
            $db->exec("SET FOREIGN_KEY_CHECKS = 1");
            Session::setFlash('error', 'Failed to delete attribute: ' . $e->getMessage(), 'danger');
        }

        $response->redirect(url('/attributes'));
    }

    public function storeValue(): void {
        $request = new Request();
        $response = new Response();
        $data = $request->getBody();

        if (empty($data['product_id']) || empty($data['attribute_id']) || empty($data['attribute_value'])) {
            Session::setFlash('error', 'Product, Attribute, and Value are required.', 'danger');
            $response->redirect(url('/products/' . ($data['product_id'] ?? '')));
        }

        $db = Database::getInstance();
        $stmt = $db->prepare("
            INSERT INTO product_attributes (product_id, attribute_id, attribute_value) 
            VALUES (:pid, :aid, :val) 
            ON DUPLICATE KEY UPDATE attribute_value = :val2
        ");
        $stmt->execute([
            'pid' => (int)$data['product_id'],
            'aid' => (int)$data['attribute_id'],
            'val' => $data['attribute_value'],
            'val2' => $data['attribute_value']
        ]);

        AuditService::log('AttributeEngine', 'SET_PRODUCT_ATTRIBUTE', (int)$data['product_id'], null, $data);
        Session::setFlash('success', 'Product attribute updated successfully.', 'success');
        $response->redirect(url('/products/' . $data['product_id']));
    }
}
